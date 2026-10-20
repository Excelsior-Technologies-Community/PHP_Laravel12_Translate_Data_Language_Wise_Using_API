<?php

namespace App\Jobs;

use App\Models\Post;
use App\Models\PostTranslation;
use App\Services\GoogleTranslateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TranslatePostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job (exponential backoff).
     */
    public array $backoff = [5, 15, 30];

    public Post $post;
    public ?array $targetLocales;

    /**
     * Create a new job instance.
     */
    public function __construct(Post $post, ?array $targetLocales = null)
    {
        $this->post = $post;
        $this->targetLocales = $targetLocales;
    }

    /**
     * Execute the job.
     */
    public function handle(GoogleTranslateService $translateService): void
    {
        // Mark post as translating
        $this->post->update(['translation_status' => 'translating']);

        try {
            $supported = $translateService->getSupportedLanguages();
            $localesToTranslate = $this->targetLocales ?: array_keys($supported);

            $translations = is_array($this->post->translations) ? $this->post->translations : [];

            foreach ($localesToTranslate as $langCode) {
                if ($langCode === 'en') {
                    $translations['en'] = [
                        'title' => $this->post->title,
                        'content' => $this->post->content,
                    ];

                    PostTranslation::updateOrCreate(
                        ['post_id' => $this->post->id, 'locale' => 'en'],
                        [
                            'title' => $this->post->title,
                            'content' => $this->post->content,
                            'status' => 'approved',
                        ]
                    );
                    continue;
                }

                $translatedTitle = $translateService->translate($this->post->title, $langCode, 'en');
                $translatedContent = $translateService->translate($this->post->content, $langCode, 'en');

                $translations[$langCode] = [
                    'title' => $translatedTitle,
                    'content' => $translatedContent,
                ];

                PostTranslation::updateOrCreate(
                    ['post_id' => $this->post->id, 'locale' => $langCode],
                    [
                        'title' => $translatedTitle,
                        'content' => $translatedContent,
                        'status' => 'pending',
                    ]
                );
            }

            // Update post with translations and mark completed
            $this->post->update([
                'translations' => $translations,
                'translation_status' => 'completed',
            ]);

            Log::info("Async translation completed for Post ID #{$this->post->id}");
        } catch (\Throwable $e) {
            Log::error("Async translation failed for Post ID #{$this->post->id}: " . $e->getMessage());
            $this->post->update(['translation_status' => 'failed']);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Job permanently failed for Post ID #{$this->post->id}: " . $exception->getMessage());
        $this->post->update(['translation_status' => 'failed']);
    }
}
