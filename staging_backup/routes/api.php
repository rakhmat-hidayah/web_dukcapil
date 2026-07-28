<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\TestApiController;

// API Version 1 Group with rate limiting and logging middleware applied
Route::middleware(['throttle:api', 'api.log'])->prefix('v1')->group(function () {
    
    // Public V1 Endpoints
    Route::get('/terms', [TestApiController::class, 'terms'])->name('api.v1.terms');
    Route::get('/news', [\App\Http\Controllers\Api\V1\NewsController::class, 'index'])->name('api.v1.news.index');
    Route::get('/news/{slug}', [\App\Http\Controllers\Api\V1\NewsController::class, 'show'])->name('api.v1.news.show');
    Route::get('/announcements', [\App\Http\Controllers\Api\V1\AnnouncementController::class, 'index'])->name('api.v1.announcements.index');
    Route::get('/gallery', [\App\Http\Controllers\Api\V1\GalleryController::class, 'index'])->name('api.v1.gallery.index');
    Route::get('/gallery/{slug}', [\App\Http\Controllers\Api\V1\GalleryController::class, 'show'])->name('api.v1.gallery.show');
    Route::get('/downloads', [\App\Http\Controllers\Api\V1\DownloadController::class, 'index'])->name('api.v1.downloads.index');
    Route::get('/faq', [\App\Http\Controllers\Api\V1\FaqController::class, 'index'])->name('api.v1.faq.index');
    Route::get('/pages/{slug}', [\App\Http\Controllers\Api\V1\PageController::class, 'show'])->name('api.v1.pages.show');
    Route::get('/menus/{location}', [\App\Http\Controllers\Api\V1\MenuController::class, 'show'])->name('api.v1.menus.show');

    // Demographics V1 Endpoints
    Route::prefix('demographics')->name('api.v1.demographics.')->group(function () {
        Route::get('/summary',       [\App\Http\Controllers\Api\V1\DemographicController::class, 'summary'])->name('summary');
        Route::get('/kecamatans',    [\App\Http\Controllers\Api\V1\DemographicController::class, 'kecamatans'])->name('kecamatans');
        Route::get('/datasets',      [\App\Http\Controllers\Api\V1\DemographicController::class, 'datasets'])->name('datasets');
        Route::get('/chart/{type}',  [\App\Http\Controllers\Api\V1\DemographicController::class, 'chartData'])->name('chart');
    });

    // Survei Kepuasan Masyarakat (IKM) V1 Endpoints
    Route::prefix('survey')->name('api.v1.survey.')->group(function () {
        Route::get('/current',     [\App\Http\Controllers\Api\SurveyApiController::class, 'current'])->name('current');
        Route::get('/results',     [\App\Http\Controllers\Api\SurveyApiController::class, 'results'])->name('results');
        Route::get('/archive',     [\App\Http\Controllers\Api\SurveyApiController::class, 'archive'])->name('archive');
        Route::get('/statistics',  [\App\Http\Controllers\Api\SurveyApiController::class, 'statistics'])->name('statistics');
    });

    // Protected V1 Endpoints (requires X-API-KEY header validation)
    Route::middleware('api.auth')->group(function () {
        Route::get('/test-auth', [TestApiController::class, 'testAuth'])->name('api.v1.test-auth');
    });
    
});
