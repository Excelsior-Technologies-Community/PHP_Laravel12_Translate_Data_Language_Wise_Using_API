<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\GoogleTranslateService;

class Post extends Model
{
    protected $fillable = ['title', 'content', 'translations'];

    protected $casts = [
        'translations' => 'array'
    ];

    public function translations()
    {
        return $this->hasMany(PostTranslation::class);
    }

    public function translateAndSave(string $locale = null)
    {
        $translateService = app(GoogleTranslateService::class);
        $supportedLanguages = $translateService->getSupportedLanguages();

        $translations = [];

        // Translate to all supported languages except source (English)
        foreach ($supportedLanguages as $langCode => $languageName) {
            if ($langCode === 'en') {
                // Store English as is
                $translations['en'] = [
                    'title' => $this->title,
                    'content' => $this->content
                ];
                continue;
            }

            // Translate title and content
            $translatedTitle = $translateService->translate($this->title, $langCode);
            $translatedContent = $translateService->translate($this->content, $langCode);

            $translations[$langCode] = [
                'title' => $translatedTitle,
                'content' => $translatedContent
            ];

            // Save to translations table
            $this->translations()->updateOrCreate(
                ['locale' => $langCode],
                [
                    'title' => $translatedTitle,
                    'content' => $translatedContent,
                    'status' => 'pending'
                ]
            );
        }

        // Update translations JSON in posts table
        $this->update(['translations' => $translations]);

        // If specific locale requested, return that translation
        if ($locale && isset($translations[$locale])) {
            return (object) $translations[$locale];
        }

        return $translations;
    }

    public function getTranslated(string $locale = 'en')
    {
        // English original content
        if ($locale === 'en') {

            return (object) [
                'title' => $this->title,
                'content' => $this->content,
                'locale' => 'en'
            ];
        }


        // Only approved translations are visible
        $translation = $this->translations()
            ->where('locale', $locale)
            ->where('status', 'approved')
            ->first();


        if ($translation) {

            return $translation;
        }


        // If translation is pending/rejected,
        // return original English content
        return (object) [
            'title' => $this->title,
            'content' => $this->content,
            'locale' => 'en'
        ];
    }
}
