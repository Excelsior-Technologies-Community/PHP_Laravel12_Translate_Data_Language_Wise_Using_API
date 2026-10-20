<?php

namespace App\Http\Controllers;

use App\Models\PostTranslation;
use App\Models\TranslationMetric;
use App\Services\GoogleTranslateService;
use Illuminate\Support\Facades\DB;

class TranslationAnalyticsController extends Controller
{
    public function index()
    {
        $translateService = app(GoogleTranslateService::class);

        // Basic counts
        $totalTranslations = PostTranslation::count();

        // Status breakdown
        $statusBreakdown = PostTranslation::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Language breakdown
        $languages = PostTranslation::select('locale', DB::raw('count(*) as total'))
            ->groupBy('locale')
            ->orderByDesc('total')
            ->get();

        $mostUsed = $languages->first();

        // Cache & Metrics from translation_metrics table
        $totalMetricRecords = TranslationMetric::count();
        $cacheHits = TranslationMetric::where('from_cache', true)->count();
        $liveCalls = TranslationMetric::where('from_cache', false)->count();

        $cacheHitRatio = $totalMetricRecords > 0
            ? round(($cacheHits / $totalMetricRecords) * 100, 1)
            : 0;

        $totalChars = (int) TranslationMetric::sum('characters_count');
        $totalWords = (int) TranslationMetric::sum('words_count');

        // Cloud Translation standard pricing estimate: ~$20 per 1,000,000 characters
        $savedCharsFromCache = (int) TranslationMetric::where('from_cache', true)->sum('characters_count');
        $estimatedCostSaved = round(($savedCharsFromCache / 1000000) * 20, 4);

        // Daily volume for the last 7 days
        $dailyVolume = TranslationMetric::select(
                DB::raw("DATE(created_at) as date"),
                DB::raw("count(*) as total_requests"),
                DB::raw("sum(case when from_cache = 1 then 1 else 0 end) as cache_hits"),
                DB::raw("sum(case when from_cache = 0 then 1 else 0 end) as live_calls"),
                DB::raw("sum(characters_count) as total_chars")
            )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy(DB::raw("DATE(created_at)"))
            ->orderBy('date')
            ->get();

        // Quota Guard
        $dailyLimit = $translateService->getDailyCharacterLimit();
        $usedToday = $translateService->getTodayCharactersUsed();
        $remainingToday = max(0, $dailyLimit - $usedToday);
        $percentUsed = min(100, round(($usedToday / max(1, $dailyLimit)) * 100, 1));

        return response()->json([
            'success' => true,
            'data' => [
                'total_translations' => $totalTranslations,
                'status_breakdown' => [
                    'approved' => $statusBreakdown['approved'] ?? 0,
                    'pending' => $statusBreakdown['pending'] ?? 0,
                    'rejected' => $statusBreakdown['rejected'] ?? 0,
                ],
                'languages' => $languages,
                'most_used_language' => $mostUsed ? $mostUsed->locale : null,
                'cache_metrics' => [
                    'total_requests' => $totalMetricRecords,
                    'cache_hits' => $cacheHits,
                    'live_api_calls' => $liveCalls,
                    'cache_hit_ratio' => $cacheHitRatio,
                    'total_characters_processed' => $totalChars,
                    'total_words_processed' => $totalWords,
                    'estimated_cost_saved_usd' => $estimatedCostSaved,
                ],
                'quota_guard' => [
                    'daily_limit_chars' => $dailyLimit,
                    'used_today_chars' => $usedToday,
                    'remaining_today_chars' => $remainingToday,
                    'percent_used' => $percentUsed,
                    'is_exceeded' => $translateService->isQuotaExceeded(),
                ],
                'daily_volume' => $dailyVolume,
            ]
        ]);
    }
}
