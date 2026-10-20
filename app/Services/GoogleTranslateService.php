<?php

namespace App\Services;

use App\Models\GlossaryTerm;
use App\Models\TranslationMetric;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GoogleTranslateService
{
    private string $apiUrl = "https://translate.googleapis.com/translate_a/single";

    protected array $supportedLanguages = [
        'en' => 'English',
        'hi' => 'Hindi',
        'gu' => 'Gujarati',
        'fr' => 'French',
        'es' => 'Spanish',
        'de' => 'German',
    ];

    /**
     * Daily translation quota in characters (e.g., 100,000 characters).
     */
    protected int $dailyCharacterLimit = 100000;

    /**
     * Translate text from source to target language with glossary protection and metrics logging.
     */
    public function translate(string $text, string $targetLang, string $sourceLang = 'en', bool $applyGlossary = true): string
    {
        if (empty(trim($text)) || $sourceLang === $targetLang) {
            return $text;
        }

        // Apply Glossary protection if enabled
        $glossaryReplacements = [];
        $processedText = $text;

        if ($applyGlossary) {
            [$processedText, $glossaryReplacements] = $this->maskGlossaryTerms($text, $targetLang);
        }

        $cacheKey = "translation_{$sourceLang}_{$targetLang}_" . md5($processedText);
        $wasCached = Cache::has($cacheKey);

        $charCount = mb_strlen($text);
        $wordCount = str_word_count(strip_tags($text));

        // Retrieve from cache or make live API call
        $translatedRaw = Cache::remember($cacheKey, now()->addDays(30), function () use ($processedText, $targetLang, $sourceLang, $charCount, $wordCount) {
            // Check daily quota
            if ($this->isQuotaExceeded()) {
                Log::warning("Translation daily quota limit reached ({$this->dailyCharacterLimit} chars). Returning fallback.");
                return $processedText;
            }

            try {
                $response = Http::timeout(8)->retry(2, 100)->get($this->apiUrl, [
                    'client' => 'gtx',
                    'sl' => $sourceLang,
                    'tl' => $targetLang,
                    'dt' => 't',
                    'q' => $processedText,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data[0]) && is_array($data[0])) {
                        // Google translate returns an array of sentence chunks
                        $combined = '';
                        foreach ($data[0] as $chunk) {
                            if (isset($chunk[0])) {
                                $combined .= $chunk[0];
                            }
                        }
                        if (!empty($combined)) {
                            // Record live API usage metric
                            $this->recordMetric('google', $sourceLang, $targetLang, $charCount, $wordCount, false);
                            return $combined;
                        }
                    }
                }

                return $processedText;
            } catch (\Throwable $e) {
                Log::error('Translation error: ' . $e->getMessage());
                return $processedText;
            }
        });

        // Record cached metric if was served from cache
        if ($wasCached) {
            $this->recordMetric('google', $sourceLang, $targetLang, $charCount, $wordCount, true);
        }

        // Restore glossary tokens to their protected or replaced forms
        if (!empty($glossaryReplacements)) {
            $translatedRaw = $this->unmaskGlossaryTerms($translatedRaw, $glossaryReplacements);
        }

        return $translatedRaw;
    }

    /**
     * Mask active glossary terms with unique tokens to prevent translation or inject custom translations.
     */
    protected function maskGlossaryTerms(string $text, string $targetLang): array
    {
        $replacements = [];

        try {
            $terms = GlossaryTerm::active()->forLocale($targetLang)->get();
            if ($terms->isEmpty()) {
                return [$text, []];
            }

            $masked = $text;
            $index = 0;

            foreach ($terms as $termObj) {
                $term = trim($termObj->term);
                if (empty($term)) continue;

                // Match whole word or phrase case-insensitively
                $pattern = '/\b' . preg_quote($term, '/') . '\b/i';
                if (preg_match($pattern, $masked)) {
                    $token = "[[GLOSSARY_{$index}]]";
                    // What to restore: custom replacement if provided, otherwise the exact original term
                    $replacementValue = !empty($termObj->replacement) ? $termObj->replacement : $term;
                    $replacements[$token] = $replacementValue;

                    $masked = preg_replace($pattern, $token, $masked);
                    $index++;
                }
            }

            return [$masked, $replacements];
        } catch (\Throwable $e) {
            Log::warning('Error masking glossary terms: ' . $e->getMessage());
            return [$text, []];
        }
    }

    /**
     * Restore glossary tokens in the translated text.
     */
    protected function unmaskGlossaryTerms(string $text, array $replacements): string
    {
        foreach ($replacements as $token => $val) {
            // Case-insensitive token replacement
            $text = str_ireplace($token, $val, $text);
            // Also handle potential whitespace inside tokens if translated
            $cleanedToken = str_replace(['[[', ']]'], ['\[\[\s*', '\s*\]\]'], preg_quote($token, '/'));
            $text = preg_replace('/' . $cleanedToken . '/i', $val, $text);
        }
        return $text;
    }

    /**
     * Record metric entry.
     */
    protected function recordMetric(string $provider, string $source, string $target, int $chars, int $words, bool $fromCache): void
    {
        try {
            TranslationMetric::create([
                'provider' => $provider,
                'source_locale' => $source,
                'target_locale' => $target,
                'characters_count' => $chars,
                'words_count' => $words,
                'from_cache' => $fromCache,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Fail silently so metrics never break application flow
        }
    }

    /**
     * Check if today's character limit is exceeded.
     */
    public function isQuotaExceeded(): bool
    {
        $todayChars = $this->getTodayCharactersUsed();
        return $todayChars >= $this->dailyCharacterLimit;
    }

    /**
     * Get characters translated today (live API only).
     */
    public function getTodayCharactersUsed(): int
    {
        try {
            return (int) TranslationMetric::whereDate('created_at', now()->today())
                ->where('from_cache', false)
                ->sum('characters_count');
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function getDailyCharacterLimit(): int
    {
        return $this->dailyCharacterLimit;
    }

    public function getSupportedLanguages(): array
    {
        return $this->supportedLanguages;
    }

    public function translateBulk(array $texts, string $targetLang, string $sourceLang = 'en'): array
    {
        $translated = [];
        foreach ($texts as $key => $text) {
            $translated[$key] = $this->translate($text, $targetLang, $sourceLang);
        }
        return $translated;
    }
}
