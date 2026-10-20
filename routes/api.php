<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserLanguageController;
use App\Http\Controllers\TranslationAnalyticsController;
use App\Http\Controllers\TranslationApprovalController;
use App\Http\Controllers\GlossaryController;
use App\Services\GoogleTranslateService;

Route::prefix('v1')->group(function () {

    Route::apiResource('posts', PostController::class);

    // Translation status & background progress check
    Route::get('posts/{id}/status', [PostController::class, 'status']);

    // Trigger individual post translation
    Route::get('posts/{id}/translate', [PostController::class, 'translatePost']);

    // Search Translation
    Route::get('posts-search', [PostController::class, 'search']);

    // User Language preference
    Route::post('user/language', [UserLanguageController::class, 'update']);
    Route::get('user/language', [UserLanguageController::class, 'show']);

    // Translation Analytics & Quota Guard
    Route::get('translation-statistics', [TranslationAnalyticsController::class, 'index']);

    // Translation Approval Workflow
    Route::get('translations/pending', [TranslationApprovalController::class, 'pending']);
    Route::post('translations/{id}/approve', [TranslationApprovalController::class, 'approve']);
    Route::post('translations/{id}/reject', [TranslationApprovalController::class, 'reject']);

    // Glossary & Brand Lock Dictionary
    Route::get('glossary', [GlossaryController::class, 'index']);
    Route::post('glossary', [GlossaryController::class, 'store']);
    Route::put('glossary/{id}', [GlossaryController::class, 'update']);
    Route::delete('glossary/{id}', [GlossaryController::class, 'destroy']);
    Route::post('glossary/test', [GlossaryController::class, 'test']);

    // Export JSON
    Route::get('posts/{id}/export', [PostController::class, 'export']);

    // Supported Languages
    Route::get('languages', function (GoogleTranslateService $service) {
        return response()->json([
            'success' => true,
            'data' => $service->getSupportedLanguages()
        ]);
    });
});
