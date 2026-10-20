<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Jobs\TranslatePostJob;
use App\Models\GlossaryTerm;
use App\Models\Post;
use App\Models\PostTranslation;
use App\Models\TranslationMetric;
use App\Services\GoogleTranslateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudioController extends Controller
{
    protected GoogleTranslateService $translateService;

    public function __construct(GoogleTranslateService $translateService)
    {
        $this->translateService = $translateService;
    }

    /**
     * Studio Dashboard Overview.
     */
    public function index()
    {
        $totalPosts = Post::count();
        $totalTranslations = PostTranslation::count();
        $pendingApprovals = PostTranslation::where('status', 'pending')->count();
        $activeGlossaryCount = GlossaryTerm::where('is_active', true)->count();

        // Recent posts with translations count
        $recentPosts = Post::with('postTranslations')->latest()->take(5)->get();

        // Pending items for quick review
        $pendingReviews = PostTranslation::with('post')
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        // Quota snapshot
        $dailyLimit = $this->translateService->getDailyCharacterLimit();
        $todayUsed = $this->translateService->getTodayCharactersUsed();
        $quotaPercent = min(100, round(($todayUsed / max(1, $dailyLimit)) * 100, 1));

        // Cache hit ratio
        $totalMetrics = TranslationMetric::count();
        $cacheHits = TranslationMetric::where('from_cache', true)->count();
        $cacheRatio = $totalMetrics > 0 ? round(($cacheHits / $totalMetrics) * 100, 1) : 0;

        $supportedLanguages = $this->translateService->getSupportedLanguages();

        return view('studio.dashboard', compact(
            'totalPosts',
            'totalTranslations',
            'pendingApprovals',
            'activeGlossaryCount',
            'recentPosts',
            'pendingReviews',
            'dailyLimit',
            'todayUsed',
            'quotaPercent',
            'cacheRatio',
            'supportedLanguages'
        ));
    }

    /**
     * Posts Management List.
     */
    public function postsIndex(Request $request)
    {
        $query = Post::with('postTranslations');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%');
        }

        $posts = $query->latest()->paginate(10)->withQueryString();
        $supportedLanguages = $this->translateService->getSupportedLanguages();

        return view('studio.posts.index', compact('posts', 'supportedLanguages'));
    }

    /**
     * Create Post Form.
     */
    public function postsCreate()
    {
        $supportedLanguages = $this->translateService->getSupportedLanguages();
        return view('studio.posts.create', compact('supportedLanguages'));
    }

    /**
     * Store New Post and Trigger Translation.
     */
    public function postsStore(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'translation_mode' => 'required|in:async,sync,none',
        ]);

        $mode = $request->input('translation_mode', 'async');

        $post = Post::create([
            'title' => $request->title,
            'content' => $request->content,
            'translation_status' => $mode === 'async' ? 'translating' : ($mode === 'sync' ? 'completed' : 'pending'),
        ]);

        if ($mode === 'async') {
            TranslatePostJob::dispatch($post);
            return redirect()->route('studio.posts.edit', $post->id)
                ->with('success', "Post created! Background translation job dispatched across all languages.");
        } elseif ($mode === 'sync') {
            $post->translateAndSave();
            return redirect()->route('studio.posts.edit', $post->id)
                ->with('success', "Post created and translated synchronously across all languages.");
        }

        // Mode 'none': just save English
        PostTranslation::create([
            'post_id' => $post->id,
            'locale' => 'en',
            'title' => $post->title,
            'content' => $post->content,
            'status' => 'approved',
        ]);

        return redirect()->route('studio.posts.edit', $post->id)
            ->with('success', "Post created successfully. You can translate it into other languages anytime.");
    }

    /**
     * Multi-Tab Translation Editor Studio.
     */
    public function postsEdit($id)
    {
        $post = Post::with('postTranslations')->findOrFail($id);
        $supportedLanguages = $this->translateService->getSupportedLanguages();

        // Index existing translations by locale
        $translations = $post->postTranslations->keyBy('locale');

        return view('studio.posts.edit', compact('post', 'supportedLanguages', 'translations'));
    }

    /**
     * Save human/manual edit to a specific language translation.
     */
    public function postsUpdateTranslation(Request $request, $postId, $locale)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'nullable|in:pending,approved,rejected',
        ]);

        $post = Post::findOrFail($postId);

        $translation = PostTranslation::updateOrCreate(
            ['post_id' => $post->id, 'locale' => $locale],
            [
                'title' => $request->title,
                'content' => $request->content,
                'status' => $request->input('status', 'approved'),
            ]
        );

        // Keep post JSON cached column updated
        $currentTranslations = is_array($post->translations) ? $post->translations : [];
        $currentTranslations[$locale] = [
            'title' => $translation->title,
            'content' => $translation->content,
        ];
        $post->update(['translations' => $currentTranslations]);

        return redirect()->route('studio.posts.edit', ['id' => $post->id, 'tab' => $locale])
            ->with('success', "Updated [{$locale}] translation successfully.");
    }

    /**
     * Trigger instant machine translation for a post.
     */
    public function translatePostNow(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        $locale = $request->input('locale'); // optional: single locale or all

        if ($locale && $locale !== 'all') {
            $translatedTitle = $this->translateService->translate($post->title, $locale, 'en');
            $translatedContent = $this->translateService->translate($post->content, $locale, 'en');

            PostTranslation::updateOrCreate(
                ['post_id' => $post->id, 'locale' => $locale],
                [
                    'title' => $translatedTitle,
                    'content' => $translatedContent,
                    'status' => 'pending',
                ]
            );

            return redirect()->route('studio.posts.edit', ['id' => $post->id, 'tab' => $locale])
                ->with('success', "AI translation for [{$locale}] generated successfully!");
        }

        // Translate all
        $post->translateAndSave();

        return redirect()->route('studio.posts.edit', $post->id)
            ->with('success', "All language translations re-generated successfully!");
    }

    /**
     * Proofreading & Approvals Workflow Studio.
     */
    public function approvalsIndex(Request $request)
    {
        $query = PostTranslation::with('post')->where('locale', '!=', 'en');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'pending');
        }

        if ($request->filled('locale') && $request->locale !== 'all') {
            $query->where('locale', $request->locale);
        }

        $translations = $query->latest()->paginate(10)->withQueryString();
        $supportedLanguages = $this->translateService->getSupportedLanguages();

        $counts = [
            'pending' => PostTranslation::where('status', 'pending')->where('locale', '!=', 'en')->count(),
            'approved' => PostTranslation::where('status', 'approved')->where('locale', '!=', 'en')->count(),
            'rejected' => PostTranslation::where('status', 'rejected')->where('locale', '!=', 'en')->count(),
        ];

        return view('studio.approvals.index', compact('translations', 'supportedLanguages', 'counts'));
    }

    /**
     * Approve Translation.
     */
    public function approveTranslation($id)
    {
        $translation = PostTranslation::findOrFail($id);
        $translation->update(['status' => 'approved']);

        return back()->with('success', "Translation (#{$id} - {$translation->locale}) approved successfully.");
    }

    /**
     * Reject Translation.
     */
    public function rejectTranslation(Request $request, $id)
    {
        $translation = PostTranslation::findOrFail($id);
        $translation->update(['status' => 'rejected']);

        return back()->with('info', "Translation (#{$id} - {$translation->locale}) rejected.");
    }

    /**
     * Glossary & Brand Lock Management Studio.
     */
    public function glossaryIndex(Request $request)
    {
        $query = GlossaryTerm::query();

        if ($request->filled('search')) {
            $query->where('term', 'like', '%' . $request->search . '%')
                  ->orWhere('replacement', 'like', '%' . $request->search . '%');
        }

        $terms = $query->latest()->paginate(15)->withQueryString();
        $supportedLanguages = $this->translateService->getSupportedLanguages();

        return view('studio.glossary.index', compact('terms', 'supportedLanguages'));
    }

    /**
     * Store new Glossary Term.
     */
    public function glossaryStore(Request $request)
    {
        $request->validate([
            'term' => 'required|string|max:255',
            'replacement' => 'nullable|string|max:255',
            'target_locale' => 'required|string',
            'description' => 'nullable|string|max:255',
        ]);

        GlossaryTerm::create([
            'term' => trim($request->term),
            'replacement' => $request->filled('replacement') ? trim($request->replacement) : null,
            'target_locale' => $request->target_locale,
            'do_not_translate' => $request->boolean('do_not_translate', true),
            'description' => $request->description,
            'is_active' => true,
        ]);

        return redirect()->route('studio.glossary')->with('success', "Glossary term '{$request->term}' registered successfully.");
    }

    /**
     * Toggle Glossary Term Active State.
     */
    public function glossaryToggle($id)
    {
        $term = GlossaryTerm::findOrFail($id);
        $term->update(['is_active' => !$term->is_active]);

        return back()->with('success', "Glossary term '{$term->term}' status updated.");
    }

    /**
     * Delete Glossary Term.
     */
    public function glossaryDestroy($id)
    {
        $term = GlossaryTerm::findOrFail($id);
        $name = $term->term;
        $term->delete();

        return back()->with('success', "Glossary term '{$name}' deleted.");
    }

    /**
     * Translation Analytics, Quota Guard & Cache Monitor View.
     */
    public function analyticsIndex()
    {
        $totalTranslations = PostTranslation::count();
        $approvedCount = PostTranslation::where('status', 'approved')->count();
        $pendingCount = PostTranslation::where('status', 'pending')->count();
        $rejectedCount = PostTranslation::where('status', 'rejected')->count();

        // Language breakdown
        $languages = PostTranslation::select('locale', DB::raw('count(*) as total'))
            ->groupBy('locale')
            ->orderByDesc('total')
            ->get();

        // Metrics from translation_metrics table
        $totalMetrics = TranslationMetric::count();
        $cacheHits = TranslationMetric::where('from_cache', true)->count();
        $liveCalls = TranslationMetric::where('from_cache', false)->count();
        $cacheRatio = $totalMetrics > 0 ? round(($cacheHits / $totalMetrics) * 100, 1) : 0;

        $totalChars = (int) TranslationMetric::sum('characters_count');
        $totalWords = (int) TranslationMetric::sum('words_count');
        $savedChars = (int) TranslationMetric::where('from_cache', true)->sum('characters_count');
        $costSaved = round(($savedChars / 1000000) * 20, 3);

        // Daily volume for Chart.js
        $dailyData = TranslationMetric::select(
                DB::raw("DATE(created_at) as date"),
                DB::raw("count(*) as total_requests"),
                DB::raw("sum(case when from_cache = 1 then 1 else 0 end) as cache_hits"),
                DB::raw("sum(case when from_cache = 0 then 1 else 0 end) as live_calls")
            )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy(DB::raw("DATE(created_at)"))
            ->orderBy('date')
            ->get();

        // Quota Guard
        $dailyLimit = $this->translateService->getDailyCharacterLimit();
        $usedToday = $this->translateService->getTodayCharactersUsed();
        $remainingToday = max(0, $dailyLimit - $usedToday);
        $percentUsed = min(100, round(($usedToday / max(1, $dailyLimit)) * 100, 1));

        return view('studio.analytics.index', compact(
            'totalTranslations',
            'approvedCount',
            'pendingCount',
            'rejectedCount',
            'languages',
            'totalMetrics',
            'cacheHits',
            'liveCalls',
            'cacheRatio',
            'totalChars',
            'totalWords',
            'savedChars',
            'costSaved',
            'dailyData',
            'dailyLimit',
            'usedToday',
            'remainingToday',
            'percentUsed'
        ));
    }
}
