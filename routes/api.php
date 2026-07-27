<?php


use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PostController;
use App\Http\Controllers\UserLanguageController;
use App\Http\Controllers\TranslationAnalyticsController;
use App\Http\Controllers\TranslationApprovalController;


Route::prefix('v1')->group(function () {



    Route::apiResource(
        'posts',
        PostController::class
    );



    Route::get(
        'posts/{id}/translate',
        [PostController::class, 'translatePost']
    );



    /*
Search Translation
*/

    Route::get(
        'posts-search',
        [PostController::class, 'search']
    );


    Route::post(
        'user/language',
        [UserLanguageController::class, 'update']
    );


    Route::get(
        'user/language',
        [UserLanguageController::class, 'show']
    );

    Route::get(
        'translation-statistics',
        [TranslationAnalyticsController::class, 'index']
    );


    /*
Translation Approval Workflow
*/


    Route::get(
        'translations/pending',
        [TranslationApprovalController::class, 'pending']
    );



    Route::post(
        'translations/{id}/approve',
        [TranslationApprovalController::class, 'approve']
    );



    Route::post(
        'translations/{id}/reject',
        [TranslationApprovalController::class, 'reject']
    );

    /*
Export JSON
*/

    Route::get(
        'posts/{id}/export',
        [PostController::class, 'export']
    );



    Route::get(
        'languages',
        function () {

            return response()->json([

                'success' => true,

                'data' => [

                    'en' => 'English',

                    'hi' => 'Hindi',

                    'gu' => 'Gujarati',

                    'fr' => 'French',

                    'es' => 'Spanish'

                ]

            ]);
        }
    );
});
