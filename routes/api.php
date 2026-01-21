<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

Route::prefix('v1')->group(function () {
    Route::apiResource('posts', PostController::class);
    
    Route::get('posts/{id}/translate', [PostController::class, 'translatePost']);
    
    // Get supported languages
    Route::get('languages', function () {
        return response()->json([
            'success' => true,
            'data' => [
                'en' => 'English',
                'hi' => 'Hindi',
                'gu' => 'Gujarati'
            ]
        ]);
    });
});