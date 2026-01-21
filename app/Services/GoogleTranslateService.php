<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class GoogleTranslateService
{
    private $apiUrl = "https://translate.googleapis.com/translate_a/single";
    
    protected $supportedLanguages = [
        'en' => 'English',
        'hi' => 'Hindi',
        'gu' => 'Gujarati'
    ];

    public function translate(string $text, string $targetLang, string $sourceLang = 'en'): string
    {
        // Cache translations to avoid repeated API calls
        $cacheKey = "translation_{$sourceLang}_{$targetLang}_" . md5($text);
        
        return Cache::remember($cacheKey, now()->addDays(30), function () use ($text, $targetLang, $sourceLang) {
            try {
                $response = Http::retry(3, 100)->get($this->apiUrl, [
                    'client' => 'gtx',
                    'sl' => $sourceLang,
                    'tl' => $targetLang,
                    'dt' => 't',
                    'q' => $text
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data[0][0][0])) {
                        return $data[0][0][0];
                    }
                }
                
                // Fallback: return original text if translation fails
                return $text;
                
            } catch (\Exception $e) {
                \Log::error('Translation error: ' . $e->getMessage());
                return $text;
            }
        });
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