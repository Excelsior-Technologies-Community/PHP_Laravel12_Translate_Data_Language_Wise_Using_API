<?php

use App\Http\Controllers\Web\StudioController;
use Illuminate\Support\Facades\Route;

// Redirect root to Translation Studio
Route::get('/', function () {
    return redirect()->route('studio.dashboard');
});

// Translation & Content Studio Routes
Route::prefix('studio')->name('studio.')->group(function () {
    Route::get('/', [StudioController::class, 'index'])->name('dashboard');

    // Posts & Multi-tab Editor
    Route::get('/posts', [StudioController::class, 'postsIndex'])->name('posts.index');
    Route::get('/posts/create', [StudioController::class, 'postsCreate'])->name('posts.create');
    Route::post('/posts', [StudioController::class, 'postsStore'])->name('posts.store');
    Route::get('/posts/{id}/edit', [StudioController::class, 'postsEdit'])->name('posts.edit');
    Route::put('/posts/{id}/translations/{locale}', [StudioController::class, 'postsUpdateTranslation'])->name('posts.translation.update');
    Route::post('/posts/{id}/translate', [StudioController::class, 'translatePostNow'])->name('posts.translate');

    // Translation Approvals & Proofreading
    Route::get('/approvals', [StudioController::class, 'approvalsIndex'])->name('approvals');
    Route::post('/approvals/{id}/approve', [StudioController::class, 'approveTranslation'])->name('approvals.approve');
    Route::post('/approvals/{id}/reject', [StudioController::class, 'rejectTranslation'])->name('approvals.reject');

    // Glossary & Brand Lock
    Route::get('/glossary', [StudioController::class, 'glossaryIndex'])->name('glossary');
    Route::post('/glossary', [StudioController::class, 'glossaryStore'])->name('glossary.store');
    Route::post('/glossary/{id}/toggle', [StudioController::class, 'glossaryToggle'])->name('glossary.toggle');
    Route::delete('/glossary/{id}', [StudioController::class, 'glossaryDestroy'])->name('glossary.destroy');

    // Analytics & Quota Guard
    Route::get('/analytics', [StudioController::class, 'analyticsIndex'])->name('analytics');
});
