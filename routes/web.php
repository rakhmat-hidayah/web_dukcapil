<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\CaptchaController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;

// SEO: Sitemap & Robots.txt
Route::get('/sitemap.xml', [\App\Http\Controllers\Public\SitemapController::class, 'sitemap'])->name('public.sitemap');
Route::get('/robots.txt', [\App\Http\Controllers\Public\SitemapController::class, 'robots'])->name('public.robots');

Route::any('/install.php', function () {
    require public_path('install.php');
    exit;
});

// Media Proxy Route for Bypassing Cloudflare WAF
Route::get('/media-file/{path}', function ($path) {
    $cleanPath = ltrim(str_replace(['../', '..\\'], '', $path), '/');
    $file = public_path('uploads/' . $cleanPath);

    if (!file_exists($file) || !is_file($file)) {
        $file = public_path('storage/' . $cleanPath);
    }

    if (!file_exists($file) || !is_file($file)) {
        abort(404);
    }

    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $mimes = [
        'webp' => 'image/webp',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
    ];

    $type = $mimes[$ext] ?? mime_content_type($file);

    return response()->file($file, [
        'Content-Type' => $type,
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->where('path', '.*')->name('public.media.proxy');

// Public User-Facing routes
Route::get('/', [\App\Http\Controllers\Public\HomeController::class, 'index'])->name('public.home');

// Enterprise Profile Module Public Routes
Route::get('/profil', [\App\Http\Controllers\Public\ProfilePageController::class, 'index'])->name('public.profile.index');
Route::get('/profil/struktur-organisasi', [\App\Http\Controllers\Public\OrganizationChartViewerController::class, 'show'])->name('public.profile.org-chart');
Route::get('/profil/pejabat', [\App\Http\Controllers\Public\OfficialDirectoryController::class, 'index'])->name('public.profile.officials.index');
Route::get('/profil/pejabat/{identifier}', [\App\Http\Controllers\Public\OfficialDirectoryController::class, 'show'])->name('public.profile.officials.show');

// Public Profile REST APIs (with throttle for rate-limiting consistency)
Route::middleware(['throttle:60,1'])->prefix('api/v1/profile')->group(function () {
    Route::get('/sections', [\App\Http\Controllers\Api\ProfileApiController::class, 'sections']);
    Route::get('/tree', [\App\Http\Controllers\Api\ProfileApiController::class, 'tree']);
    Route::get('/officials', [\App\Http\Controllers\Api\ProfileApiController::class, 'officials']);
    Route::get('/officials/{identifier}', [\App\Http\Controllers\Api\ProfileApiController::class, 'officialDetail']);
});
Route::get('/news', [\App\Http\Controllers\Public\NewsController::class, 'index'])->name('public.news.index');
Route::get('/berita', [\App\Http\Controllers\Public\NewsController::class, 'index']);
Route::get('/news/tag/{slug}', [\App\Http\Controllers\Public\NewsController::class, 'tagArchive'])->name('public.news.tag');
Route::get('/tag/{slug}', [\App\Http\Controllers\Public\NewsController::class, 'tagArchive']);
Route::get('/news/{slug}/og-image.png', [\App\Http\Controllers\Public\NewsController::class, 'ogBanner'])->name('public.news.og-banner');
Route::get('/news/{slug}', [\App\Http\Controllers\Public\NewsController::class, 'show'])->name('public.news.show');
Route::get('/berita/{slug}', [\App\Http\Controllers\Public\NewsController::class, 'show']);
Route::post('/news/{id}/share-record', [\App\Http\Controllers\Public\NewsController::class, 'trackShare'])->name('public.news.track-share');
Route::post('/news/{news}/react', [\App\Http\Controllers\Public\NewsController::class, 'postReaction'])->name('public.news.react');
Route::get('/gallery', [\App\Http\Controllers\Public\GalleryController::class, 'index'])->name('public.gallery.index');
Route::get('/gallery/{slug}', [\App\Http\Controllers\Public\GalleryController::class, 'show'])->name('public.gallery.show');
Route::get('/downloads', [\App\Http\Controllers\Public\DownloadCenterController::class, 'index'])->name('public.downloads.index');
Route::get('/downloads/check/{id}', [\App\Http\Controllers\Public\DownloadController::class, 'check'])->name('public.downloads.check');
Route::get('/downloads/stream/{id}', [\App\Http\Controllers\Public\DownloadController::class, 'stream'])->name('public.downloads.stream');
Route::get('/contact', [\App\Http\Controllers\Public\ContactController::class, 'index'])->name('public.contact');
Route::get('/search', [\App\Http\Controllers\Public\SearchController::class, 'search'])->name('public.search');
Route::get('/statistik-kependudukan', [\App\Http\Controllers\Public\DemographicController::class, 'statistics'])->name('public.statistics');

// ── Online Queue & Document Pre-verification (Public Portal) ─────────
Route::prefix('layanan/antrian')->name('queue.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Public\Queue\QueueInfoController::class, 'index'])->name('index');
    Route::post('/cek-status', [\App\Http\Controllers\Public\Queue\QueueInfoController::class, 'checkStatus'])->name('check');
    Route::get('/daftar', [\App\Http\Controllers\Public\Queue\QueueApplicationController::class, 'create'])->name('apply.create');
    Route::post('/daftar', [\App\Http\Controllers\Public\Queue\QueueApplicationController::class, 'storeDraft'])->name('apply.store');
    Route::get('/daftar/{regNumber}/dokumen', [\App\Http\Controllers\Public\Queue\QueueApplicationController::class, 'showDocuments'])->name('apply.documents');
    Route::post('/daftar/{regNumber}/dokumen', [\App\Http\Controllers\Public\Queue\QueueApplicationController::class, 'uploadDocument'])->name('apply.upload-doc');
    Route::delete('/daftar/{regNumber}/dokumen/{documentId}', [\App\Http\Controllers\Public\Queue\QueueApplicationController::class, 'removeDocument'])->name('apply.remove-doc');
    Route::post('/daftar/{regNumber}/submit', [\App\Http\Controllers\Public\Queue\QueueApplicationController::class, 'submit'])->name('apply.submit');
    Route::get('/daftar/{regNumber}/dokumen/{documentId}/preview', [\App\Http\Controllers\Public\Queue\QueueApplicationController::class, 'serveDraftDocument'])->name('apply.preview-doc');
    
    // Status tracking, schedule booking & re-submission
    Route::get('/status/{token}', [\App\Http\Controllers\Public\Queue\ApplicantDashboardController::class, 'show'])->name('status');
    Route::get('/status/{token}/booking', [\App\Http\Controllers\Public\Queue\ApplicantDashboardController::class, 'showBooking'])->name('booking');
    Route::get('/status/{token}/booking-show', [\App\Http\Controllers\Public\Queue\ApplicantDashboardController::class, 'showBooking'])->name('booking.show');
    Route::post('/status/{token}/booking', [\App\Http\Controllers\Public\Queue\ApplicantDashboardController::class, 'storeBooking'])->name('booking.store');
    Route::post('/status/{token}/revisi', [\App\Http\Controllers\Public\Queue\ApplicantDashboardController::class, 'resubmit'])->name('resubmit');
    Route::post('/status/{token}/batal', [\App\Http\Controllers\Public\Queue\ApplicantDashboardController::class, 'cancel'])->name('cancel');
    Route::get('/status/{token}/dokumen/{documentId}', [\App\Http\Controllers\Public\Queue\ApplicantDashboardController::class, 'serveDocument'])->name('serve-doc');
});

Route::get('/layanan', [\App\Http\Controllers\Public\ServiceRequirementController::class, 'index'])->name('public.services.index');
Route::get('/layanan/survei', [\App\Http\Controllers\Public\PublicSurveyController::class, 'index'])->name('public.survey.index');
Route::post('/layanan/survei', [\App\Http\Controllers\Public\PublicSurveyController::class, 'store'])->name('public.survey.store');
Route::get('/layanan/mobile', [\App\Http\Controllers\Public\MobileServiceController::class, 'index'])->name('public.mobile-services.index');
Route::redirect('/layanan/mobil', '/layanan/mobile');
Route::get('/layanan/{slug}', [\App\Http\Controllers\Public\ServiceRequirementController::class, 'show'])->name('public.services.show');
Route::get('/inovasi', [\App\Http\Controllers\Public\InnovationController::class, 'index'])->name('public.innovations.index');
Route::get('/inovasi/{slug}', [\App\Http\Controllers\Public\InnovationController::class, 'show'])->name('public.innovations.show');

// Public FAQ
Route::get('/faq', [\App\Http\Controllers\Public\FaqController::class, 'index'])->name('public.faq');

// Public PPID
Route::get('/ppid', function () { return redirect()->route('public.ppid.page', 'pengertian'); })->name('public.ppid');
Route::get('/ppid/pengertian', [\App\Http\Controllers\Public\PpidController::class, 'page'])->defaults('slug', 'pengertian')->name('public.ppid.pengertian');
Route::get('/ppid/profil', [\App\Http\Controllers\Public\PpidController::class, 'page'])->defaults('slug', 'profil')->name('public.ppid.profil');
Route::get('/ppid/tugas-fungsi', [\App\Http\Controllers\Public\PpidController::class, 'page'])->defaults('slug', 'tugas-fungsi')->name('public.ppid.tugas-fungsi');
Route::get('/ppid/kontak', [\App\Http\Controllers\Public\PpidController::class, 'page'])->defaults('slug', 'kontak')->name('public.ppid.kontak');
Route::get('/ppid/sk-ppid', [\App\Http\Controllers\Public\PpidController::class, 'page'])->defaults('slug', 'sk-ppid')->name('public.ppid.sk-ppid');
Route::get('/ppid/informasi-publik', [\App\Http\Controllers\Public\PpidController::class, 'informasiPublik'])->name('public.ppid.informasi-publik');
Route::get('/ppid/prosedur', [\App\Http\Controllers\Public\PpidController::class, 'prosedur'])->name('public.ppid.prosedur');
Route::get('/ppid/layanan-informasi', [\App\Http\Controllers\Public\PpidController::class, 'layananInformasi'])->name('public.ppid.layanan-informasi');
Route::get('/ppid/permohonan', [\App\Http\Controllers\Public\PpidController::class, 'requestForm'])->name('public.ppid.request');
Route::post('/ppid/permohonan', [\App\Http\Controllers\Public\PpidController::class, 'submitRequest'])->name('public.ppid.request.submit');
Route::get('/ppid/permohonan/sukses/{ticket}', [\App\Http\Controllers\Public\PpidController::class, 'requestSuccess'])->name('public.ppid.request.success');
Route::get('/ppid/lacak', [\App\Http\Controllers\Public\PpidController::class, 'trackRequest'])->name('public.ppid.track');
Route::get('/ppid/{slug}', [\App\Http\Controllers\Public\PpidController::class, 'page'])->name('public.ppid.page');
Route::get('/ppid-dokumen/{document}/download', [\App\Http\Controllers\Public\PpidController::class, 'downloadDocument'])->name('public.ppid.document.download');
Route::get('/ppid-response/{request}/download', [\App\Http\Controllers\Public\PpidController::class, 'downloadResponse'])->name('public.ppid.response.download');

// Public Complaint System
Route::get('/pengaduan', [\App\Http\Controllers\Public\ComplaintController::class, 'create'])->name('public.complaint.create');
Route::post('/pengaduan', [\App\Http\Controllers\Public\ComplaintController::class, 'store'])->name('public.complaint.store');
Route::get('/pengaduan/sukses/{ticket}', [\App\Http\Controllers\Public\ComplaintController::class, 'success'])->name('public.complaint.success');
Route::get('/pengaduan/lacak', [\App\Http\Controllers\Public\ComplaintController::class, 'trackForm'])->name('public.complaint.track');
Route::post('/pengaduan/lacak', [\App\Http\Controllers\Public\ComplaintController::class, 'trackSearch'])->name('public.complaint.track.search');
Route::get('/pengaduan/status/{ticket}', [\App\Http\Controllers\Public\ComplaintController::class, 'status'])->name('public.complaint.status');

// CAPTCHA Routes
Route::get('/captcha/math', [CaptchaController::class, 'math'])->name('captcha.math');
Route::get('/captcha/image', [CaptchaController::class, 'image'])->name('captcha.image');

// API Documentation routes
Route::get('/api/docs', [\App\Http\Controllers\Api\DocController::class, 'docs'])->name('api.docs');
Route::get('/api/docs/json', [\App\Http\Controllers\Api\DocController::class, 'json'])->name('api.docs.json');

// Fallback named route for standard Laravel login route() calls
Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

// Admin Portal Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });

    // Guest Admin
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.store');

        // Two-Factor Authentication Challenge (Step 2 of Login)
        Route::get('/login/two-factor', [AuthController::class, 'showTwoFactorChallenge'])->name('login.two-factor');
        Route::post('/login/two-factor', [AuthController::class, 'verifyTwoFactorChallenge'])->name('login.two-factor.store');
    });

    // Authenticated Admin
    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Account Security & 2FA Management (Available for all authenticated operators)
        Route::get('/security', [\App\Http\Controllers\Admin\TwoFactorController::class, 'index'])->name('security.index');
        Route::post('/security/2fa/setup', [\App\Http\Controllers\Admin\TwoFactorController::class, 'setup'])->name('security.2fa.setup');
        Route::post('/security/2fa/confirm', [\App\Http\Controllers\Admin\TwoFactorController::class, 'confirm'])->name('security.2fa.confirm');
        Route::post('/security/2fa/disable', [\App\Http\Controllers\Admin\TwoFactorController::class, 'disable'])->name('security.2fa.disable');
        Route::post('/security/2fa/recovery-codes', [\App\Http\Controllers\Admin\TwoFactorController::class, 'regenerateRecoveryCodes'])->name('security.2fa.recovery-codes');

        // Notifications CMS (JSON APIs for all authenticated operators)
        Route::get('/notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{id}/read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [\App\Http\Controllers\Admin\NotificationController::class, 'markAllRead'])->name('notifications.read-all');

        // -------------------------------------------------------------
        // Super Administrator Only Routes
        // -------------------------------------------------------------
        Route::middleware('role:Super Administrator')->group(function () {
            // User Management CMS
            Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
            Route::post('/users', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
            Route::put('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
            Route::delete('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
            Route::post('/users/{user}/toggle-active', [\App\Http\Controllers\Admin\UserController::class, 'toggleActive'])->name('users.toggle-active');
            Route::post('/users/{user}/admin-reset-2fa', [\App\Http\Controllers\Admin\TwoFactorController::class, 'adminReset'])->name('users.admin-reset-2fa');

            // Theme Customizer CMS
            Route::get('/theme', [\App\Http\Controllers\Admin\ThemeController::class, 'index'])->name('theme.index');
            Route::post('/theme', [\App\Http\Controllers\Admin\ThemeController::class, 'update'])->name('theme.update');

            // Audit Logs CMS
            Route::get('/audit-logs', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('audit-logs.index');

            // API Key Management CMS
            Route::get('/api-keys', [\App\Http\Controllers\Admin\ApiKeyController::class, 'index'])->name('api-keys.index');
            Route::post('/api-keys', [\App\Http\Controllers\Admin\ApiKeyController::class, 'store'])->name('api-keys.store');
            Route::post('/api-keys/{apiKey}/regenerate', [\App\Http\Controllers\Admin\ApiKeyController::class, 'regenerate'])->name('api-keys.regenerate');
            Route::post('/api-keys/{apiKey}/toggle', [\App\Http\Controllers\Admin\ApiKeyController::class, 'toggleStatus'])->name('api-keys.toggle');
            Route::delete('/api-keys/{apiKey}', [\App\Http\Controllers\Admin\ApiKeyController::class, 'destroy'])->name('api-keys.destroy');

            // API Monitor Dashboard & Settings
            Route::get('/api-dashboard', [\App\Http\Controllers\Admin\ApiDashboardController::class, 'index'])->name('api-dashboard.index');
            Route::get('/api-settings', [\App\Http\Controllers\Admin\ApiSettingController::class, 'index'])->name('api-settings.index');
            Route::post('/api-settings', [\App\Http\Controllers\Admin\ApiSettingController::class, 'update'])->name('api-settings.update');

            // External Services & Office Contact CMS
            Route::get('/external-services', [\App\Http\Controllers\Admin\ExternalServiceController::class, 'index'])->name('external-services.index');
            Route::post('/external-services', [\App\Http\Controllers\Admin\ExternalServiceController::class, 'update'])->name('external-services.update');
            Route::get('/office-contact', [\App\Http\Controllers\Admin\OfficeContactController::class, 'index'])->name('office-contact.index');
            Route::post('/office-contact', [\App\Http\Controllers\Admin\OfficeContactController::class, 'update'])->name('office-contact.update');

            // Notification & Integration System Settings
            Route::get('/notification-settings', [\App\Http\Controllers\Admin\NotificationSettingController::class, 'index'])->name('notification-settings.index');
            Route::post('/notification-settings', [\App\Http\Controllers\Admin\NotificationSettingController::class, 'update'])->name('notification-settings.update');
            Route::post('/notification-settings/test', [\App\Http\Controllers\Admin\NotificationSettingController::class, 'sendTestEmail'])->name('notification-settings.test');

            // System Health Dashboard
            Route::get('/system/health', [\App\Http\Controllers\Admin\SystemHealthController::class, 'index'])->name('system.health');
        });

        // Visitor Analytics Module (Super Administrator & Website Administrator)
        Route::middleware('role:Super Administrator|Website Administrator')->group(function () {
            Route::get('/visitor-analytics', [\App\Http\Controllers\Admin\VisitorAnalyticsController::class, 'index'])->name('visitor-analytics.index');
            Route::get('/visitor-analytics/export', [\App\Http\Controllers\Admin\VisitorAnalyticsController::class, 'export'])->name('visitor-analytics.export');
            Route::get('/visitor-analytics/{id}', [\App\Http\Controllers\Admin\VisitorAnalyticsController::class, 'show'])->name('visitor-analytics.show');
            Route::post('/visitor-analytics/{id}/retry-geolocation', [\App\Http\Controllers\Admin\VisitorAnalyticsController::class, 'retryGeolocation'])->name('visitor-analytics.retry-geolocation');

            // Visitor Analytics Retention Settings
            Route::get('/visitor-analytics-retention', [\App\Http\Controllers\Admin\VisitorRetentionController::class, 'index'])->name('visitor-analytics-retention.index');
            Route::post('/visitor-analytics-retention/settings', [\App\Http\Controllers\Admin\VisitorRetentionController::class, 'updateSettings'])->name('visitor-analytics-retention.settings');
            Route::post('/visitor-analytics-retention/cleanup-now', [\App\Http\Controllers\Admin\VisitorRetentionController::class, 'cleanupNow'])->name('visitor-analytics-retention.cleanup-now');
            Route::get('/visitor-analytics-retention/cleanup-status', [\App\Http\Controllers\Admin\VisitorRetentionController::class, 'cleanupStatus'])->name('visitor-analytics-retention.cleanup-status');
        });

        // -------------------------------------------------------------
        // Content & Media, Profile, Services, Demographics Route Group
        // -------------------------------------------------------------
        Route::middleware('role:Super Administrator|Website Administrator|Publication Operator|Content Editor|PPID Admin')->group(function () {
            // Enterprise Profile Module CMS
            Route::prefix('profile')->name('profile.')->group(function () {
                Route::get('/dashboard', [\App\Http\Controllers\Admin\Profile\ProfileDashboardController::class, 'index'])->name('dashboard');
                Route::get('/builder', [\App\Http\Controllers\Admin\Profile\ProfileBuilderController::class, 'index'])->name('builder');
                Route::post('/builder/reorder', [\App\Http\Controllers\Admin\Profile\ProfileBuilderController::class, 'reorder'])->name('builder.reorder');
                Route::post('/builder/section/{section}/setting', [\App\Http\Controllers\Admin\Profile\ProfileBuilderController::class, 'updateSetting'])->name('builder.setting.update');
                Route::resource('/officials', \App\Http\Controllers\Admin\Profile\OfficialController::class);
                Route::get('/organization-chart', [\App\Http\Controllers\Admin\Profile\OrganizationChartController::class, 'index'])->name('org-chart.index');
                Route::post('/organization-chart/node', [\App\Http\Controllers\Admin\Profile\OrganizationChartController::class, 'storeNode'])->name('org-chart.node.store');
                Route::put('/organization-chart/node/{node}', [\App\Http\Controllers\Admin\Profile\OrganizationChartController::class, 'updateNode'])->name('org-chart.node.update');
                Route::delete('/organization-chart/node/{node}', [\App\Http\Controllers\Admin\Profile\OrganizationChartController::class, 'destroyNode'])->name('org-chart.node.destroy');
            });

            // File Manager CMS
            Route::get('/files', [\App\Http\Controllers\Admin\FileManagerController::class, 'index'])->name('files.index');
            Route::get('/file-manager', function () { return redirect()->route('admin.files.index'); });
            Route::get('/filemanager', function () { return redirect()->route('admin.files.index'); });
            Route::get('/media', function () { return redirect()->route('admin.files.index'); });
            Route::post('/files/folder', [\App\Http\Controllers\Admin\FileManagerController::class, 'createFolder'])->name('files.folder.create');
            Route::post('/files/upload', [\App\Http\Controllers\Admin\FileManagerController::class, 'uploadFile'])->name('files.upload');
            Route::post('/files/{file}/version', [\App\Http\Controllers\Admin\FileManagerController::class, 'uploadVersion'])->name('files.version.upload');
            Route::delete('/files/{file}', [\App\Http\Controllers\Admin\FileManagerController::class, 'destroyFile'])->name('files.destroy');
            Route::delete('/files/folder/{folder}', [\App\Http\Controllers\Admin\FileManagerController::class, 'destroyFolder'])->name('files.folder.destroy');

            // Content CMS Modules
            Route::get('/informasi-layanan', [\App\Http\Controllers\Admin\InformasiLayananController::class, 'index'])->name('informasi-layanan.index');
            Route::post('/informasi-layanan', [\App\Http\Controllers\Admin\InformasiLayananController::class, 'update'])->name('informasi-layanan.update');
            Route::resource('/pages', \App\Http\Controllers\Admin\PageController::class)->except(['show']);

            Route::get('/news/categories', [\App\Http\Controllers\Admin\NewsController::class, 'categories'])->name('news.categories');
            Route::get('/news/tags/search', [\App\Http\Controllers\Admin\NewsController::class, 'searchTags'])->name('news.tags.search');
            Route::post('/news/categories', [\App\Http\Controllers\Admin\NewsController::class, 'storeCategory'])->name('news.categories.store');
            Route::put('/news/categories/{newsCategory}', [\App\Http\Controllers\Admin\NewsController::class, 'updateCategory'])->name('news.categories.update');
            Route::delete('/news/categories/{newsCategory}', [\App\Http\Controllers\Admin\NewsController::class, 'destroyCategory'])->name('news.categories.destroy');
            Route::post('/news/{id}/restore', [\App\Http\Controllers\Admin\NewsController::class, 'restore'])->name('news.restore');
            Route::post('/news/upload-image', [\App\Http\Controllers\Admin\NewsController::class, 'uploadInlineImage'])->name('news.upload-image');
            Route::get('/news/share-stats', [\App\Http\Controllers\Admin\NewsController::class, 'shareAnalytics'])->name('news.share-analytics');
            Route::get('/news/reader-stats', [\App\Http\Controllers\Admin\NewsController::class, 'readerAnalytics'])->name('news.reader-analytics');
            Route::resource('/news', \App\Http\Controllers\Admin\NewsController::class)->except(['show']);

            Route::resource('/announcements', \App\Http\Controllers\Admin\AnnouncementController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::resource('/banners', \App\Http\Controllers\Admin\BannerController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::post('/banners/reorder', [\App\Http\Controllers\Admin\BannerController::class, 'reorder'])->name('banners.reorder');

            Route::get('/gallery', [\App\Http\Controllers\Admin\GalleryController::class, 'index'])->name('gallery.index');
            Route::get('/gallery/{galleryAlbum}', [\App\Http\Controllers\Admin\GalleryController::class, 'show'])->name('gallery.show');
            Route::post('/gallery/album', [\App\Http\Controllers\Admin\GalleryController::class, 'storeAlbum'])->name('gallery.album.store');
            Route::put('/gallery/album/{galleryAlbum}', [\App\Http\Controllers\Admin\GalleryController::class, 'updateAlbum'])->name('gallery.album.update');
            Route::delete('/gallery/album/{galleryAlbum}', [\App\Http\Controllers\Admin\GalleryController::class, 'destroyAlbum'])->name('gallery.album.destroy');
            Route::post('/gallery/{galleryAlbum}/upload', [\App\Http\Controllers\Admin\GalleryController::class, 'uploadItem'])->name('gallery.item.upload');
            Route::delete('/gallery/item/{galleryItem}', [\App\Http\Controllers\Admin\GalleryController::class, 'destroyItem'])->name('gallery.item.destroy');

            Route::resource('/faqs', \App\Http\Controllers\Admin\FaqController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::post('/faqs/reorder', [\App\Http\Controllers\Admin\FaqController::class, 'reorder'])->name('faqs.reorder');

            Route::get('/menus', [\App\Http\Controllers\Admin\MenuController::class, 'index'])->name('menus.index');
            Route::post('/menus', [\App\Http\Controllers\Admin\MenuController::class, 'store'])->name('menus.store');
            Route::delete('/menus/{menu}', [\App\Http\Controllers\Admin\MenuController::class, 'destroy'])->name('menus.destroy');
            Route::post('/menus/{menu}/item', [\App\Http\Controllers\Admin\MenuController::class, 'storeItem'])->name('menus.item.store');
            Route::put('/menus/item/{menuItem}', [\App\Http\Controllers\Admin\MenuController::class, 'updateItem'])->name('menus.item.update');
            Route::delete('/menus/item/{menuItem}', [\App\Http\Controllers\Admin\MenuController::class, 'destroyItem'])->name('menus.item.destroy');
            Route::post('/menus/{menu}/reorder', [\App\Http\Controllers\Admin\MenuController::class, 'reorderItems'])->name('menus.reorder');

            Route::get('/demographics/hierarchy', [\App\Http\Controllers\Admin\DemographicController::class, 'hierarchy'])->name('demographics.hierarchy');
            Route::get('/demographics/hierarchy/{kecamatan}/desas', [\App\Http\Controllers\Admin\DemographicController::class, 'desasByKecamatan'])->name('demographics.desas');
            Route::post('/demographics/kecamatan', [\App\Http\Controllers\Admin\DemographicController::class, 'storeKecamatan'])->name('demographics.kecamatan.store');
            Route::put('/demographics/kecamatan/{kecamatan}', [\App\Http\Controllers\Admin\DemographicController::class, 'updateKecamatan'])->name('demographics.kecamatan.update');
            Route::delete('/demographics/kecamatan/{kecamatan}', [\App\Http\Controllers\Admin\DemographicController::class, 'destroyKecamatan'])->name('demographics.kecamatan.destroy');
            Route::post('/demographics/desa', [\App\Http\Controllers\Admin\DemographicController::class, 'storeDesa'])->name('demographics.desa.store');
            Route::put('/demographics/desa/{desa}', [\App\Http\Controllers\Admin\DemographicController::class, 'updateDesa'])->name('demographics.desa.update');
            Route::delete('/demographics/desa/{desa}', [\App\Http\Controllers\Admin\DemographicController::class, 'destroyDesa'])->name('demographics.desa.destroy');
            Route::get('/demographics/datasets', [\App\Http\Controllers\Admin\DemographicController::class, 'datasets'])->name('demographics.datasets');
            Route::post('/demographics/datasets', [\App\Http\Controllers\Admin\DemographicController::class, 'storeDataset'])->name('demographics.datasets.store');
            Route::put('/demographics/datasets/{dataset}', [\App\Http\Controllers\Admin\DemographicController::class, 'updateDataset'])->name('demographics.datasets.update');
            Route::delete('/demographics/datasets/{dataset}', [\App\Http\Controllers\Admin\DemographicController::class, 'destroyDataset'])->name('demographics.datasets.destroy');
            Route::post('/demographics/filter-settings', [\App\Http\Controllers\Admin\DemographicController::class, 'updateFilterSettings'])->name('demographics.filter-settings.update');
            Route::get('/demographics/dashboard', [\App\Http\Controllers\Admin\DemographicController::class, 'dashboard'])->name('demographics.dashboard');

            Route::resource('/service-requirements', \App\Http\Controllers\Admin\ServiceRequirementController::class)->except(['show']);
            Route::resource('/innovations', \App\Http\Controllers\Admin\InnovationController::class)->except(['show']);
            Route::resource('/downloads', \App\Http\Controllers\Admin\DownloadController::class)->except(['show']);
        });

        // -------------------------------------------------------------
        // Complaint Officer, Website Administrator & Super Admin Routes
        // -------------------------------------------------------------
        Route::middleware('role:Super Administrator|Website Administrator|Complaint Officer')->group(function () {
            Route::get('/complaints', [\App\Http\Controllers\Admin\ComplaintController::class, 'index'])->name('complaints.index');
            Route::get('/complaints/categories', [\App\Http\Controllers\Admin\ComplaintController::class, 'categories'])->name('complaints.categories');
            Route::post('/complaints/categories', [\App\Http\Controllers\Admin\ComplaintController::class, 'storeCategory'])->name('complaints.categories.store');
            Route::put('/complaints/categories/{category}', [\App\Http\Controllers\Admin\ComplaintController::class, 'updateCategory'])->name('complaints.categories.update');
            Route::delete('/complaints/categories/{category}', [\App\Http\Controllers\Admin\ComplaintController::class, 'destroyCategory'])->name('complaints.categories.destroy');
            Route::get('/complaints/{complaint}', [\App\Http\Controllers\Admin\ComplaintController::class, 'show'])->name('complaints.show');
            Route::post('/complaints/{complaint}/reply', [\App\Http\Controllers\Admin\ComplaintController::class, 'reply'])->name('complaints.reply');
            Route::post('/complaints/{complaint}/status', [\App\Http\Controllers\Admin\ComplaintController::class, 'changeStatus'])->name('complaints.status');
            Route::post('/complaints/{complaint}/assign', [\App\Http\Controllers\Admin\ComplaintController::class, 'assign'])->name('complaints.assign');

            Route::prefix('surveys')->name('surveys.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\SurveyController::class, 'index'])->name('index');
                Route::post('/periods', [\App\Http\Controllers\Admin\SurveyController::class, 'storePeriod'])->name('periods.store');
                Route::put('/periods/{period}', [\App\Http\Controllers\Admin\SurveyController::class, 'updatePeriod'])->name('periods.update');
                Route::post('/periods/{period}/questions', [\App\Http\Controllers\Admin\SurveyController::class, 'storeQuestion'])->name('questions.store');
                Route::put('/questions/{question}', [\App\Http\Controllers\Admin\SurveyController::class, 'updateQuestion'])->name('questions.update');
                Route::delete('/questions/{question}', [\App\Http\Controllers\Admin\SurveyController::class, 'destroyQuestion'])->name('questions.destroy');
                Route::post('/periods/{period}/recommendations', [\App\Http\Controllers\Admin\SurveyController::class, 'storeRecommendation'])->name('recommendations.store');
                Route::put('/recommendations/{recommendation}', [\App\Http\Controllers\Admin\SurveyController::class, 'updateRecommendation'])->name('recommendations.update');
                Route::delete('/recommendations/{recommendation}', [\App\Http\Controllers\Admin\SurveyController::class, 'destroyRecommendation'])->name('recommendations.destroy');
                Route::post('/periods/{period}/follow-up', [\App\Http\Controllers\Admin\SurveyController::class, 'storeFollowUpAction'])->name('follow-up.store');
                Route::put('/follow-up/{followUpAction}', [\App\Http\Controllers\Admin\SurveyController::class, 'updateFollowUpAction'])->name('follow-up.update');
                Route::delete('/follow-up/{followUpAction}', [\App\Http\Controllers\Admin\SurveyController::class, 'destroyFollowUpAction'])->name('follow-up.destroy');
                Route::post('/periods/{period}/recalculate', [\App\Http\Controllers\Admin\SurveyController::class, 'recalculate'])->name('recalculate');
                Route::post('/periods/{period}/copy-questions', [\App\Http\Controllers\Admin\SurveyController::class, 'copyQuestions'])->name('questions.copy');
                Route::delete('/responses/{response}', [\App\Http\Controllers\Admin\SurveyController::class, 'destroyResponse'])->name('responses.destroy');
            });
        });

        // -------------------------------------------------------------
        // Downloads Management (Super Admin, Website Admin, Publication Operator, Content Editor & PPID Admin)
        // -------------------------------------------------------------
        Route::middleware('role:Super Administrator|Website Administrator|Publication Operator|Content Editor|PPID Admin')->group(function () {
            Route::get('/downloads/{id}/check', [\App\Http\Controllers\Admin\DownloadController::class, 'check'])->name('downloads.check');
            Route::get('/downloads/{id}/download', [\App\Http\Controllers\Admin\DownloadController::class, 'download'])->name('downloads.download');
            Route::post('/downloads/categories', [\App\Http\Controllers\Admin\DownloadController::class, 'storeCategory'])->name('downloads.categories.store');
            Route::delete('/downloads/categories/{downloadCategory}', [\App\Http\Controllers\Admin\DownloadController::class, 'destroyCategory'])->name('downloads.categories.destroy');
        });

        // -------------------------------------------------------------
        // PPID Management (Super Administrator, Website Administrator & PPID Admin)
        // -------------------------------------------------------------
        Route::middleware('role:Super Administrator|Website Administrator|PPID Admin')->group(function () {
            // PPID Management
            Route::prefix('ppid')->name('ppid.')->group(function () {
                Route::get('/pages', [\App\Http\Controllers\Admin\PpidController::class, 'pagesIndex'])->name('pages.index');
                Route::get('/pages/{page}/edit', [\App\Http\Controllers\Admin\PpidController::class, 'pagesEdit'])->name('pages.edit');
                Route::put('/pages/{page}', [\App\Http\Controllers\Admin\PpidController::class, 'pagesUpdate'])->name('pages.update');
                Route::get('/documents', [\App\Http\Controllers\Admin\PpidController::class, 'documentsIndex'])->name('documents.index');
                Route::get('/documents/create', [\App\Http\Controllers\Admin\PpidController::class, 'documentsCreate'])->name('documents.create');
                Route::post('/documents', [\App\Http\Controllers\Admin\PpidController::class, 'documentsStore'])->name('documents.store');
                Route::get('/documents/{document}/edit', [\App\Http\Controllers\Admin\PpidController::class, 'documentsEdit'])->name('documents.edit');
                Route::put('/documents/{document}', [\App\Http\Controllers\Admin\PpidController::class, 'documentsUpdate'])->name('documents.update');
                Route::delete('/documents/{document}', [\App\Http\Controllers\Admin\PpidController::class, 'documentsDestroy'])->name('documents.destroy');
                Route::get('/requests', [\App\Http\Controllers\Admin\PpidController::class, 'requestsIndex'])->name('requests.index');
                Route::get('/requests/{request}', [\App\Http\Controllers\Admin\PpidController::class, 'requestsShow'])->name('requests.show');
                Route::post('/requests/{ppidRequest}/respond', [\App\Http\Controllers\Admin\PpidController::class, 'requestsRespond'])->name('requests.respond');
                Route::delete('/requests/{request}', [\App\Http\Controllers\Admin\PpidController::class, 'requestsDestroy'])->name('requests.destroy');
            });
        });

        // -------------------------------------------------------------
        // Online Queue Management & Pre-Verification (Admin CMS)
        // -------------------------------------------------------------
        Route::middleware('role_or_permission:Super Administrator|Website Administrator|Queue Officer|Queue Verifier|manage_online_queue|manage_queue_verification|view_queue_reports')
            ->prefix('queue')->name('queue.')->group(function () {

            Route::get('/dashboard', [\App\Http\Controllers\Admin\Queue\QueueDashboardController::class, 'index'])->name('dashboard');

            // Legacy Counter Route (Redirect to Serah Terima)
            Route::get('/counter', function () {
                return redirect()->route('admin.handover.dashboard');
            })->name('counter.index');

            // Applications & Online Document Verification
            Route::get('/applications', [\App\Http\Controllers\Admin\Queue\ApplicationVerificationController::class, 'index'])->name('applications.index');
            Route::get('/applications/{id}', [\App\Http\Controllers\Admin\Queue\ApplicationVerificationController::class, 'show'])->name('applications.show');
            Route::post('/applications/{id}/pre-verify', [\App\Http\Controllers\Admin\Queue\ApplicationVerificationController::class, 'preVerify'])->name('applications.pre-verify');
            Route::post('/applications/{id}/approve', [\App\Http\Controllers\Admin\Queue\ApplicationVerificationController::class, 'approve'])->name('applications.approve');
            Route::post('/applications/{id}/revision', [\App\Http\Controllers\Admin\Queue\ApplicationVerificationController::class, 'requestRevision'])->name('applications.revision');
            Route::post('/applications/{id}/reject', [\App\Http\Controllers\Admin\Queue\ApplicationVerificationController::class, 'reject'])->name('applications.reject');
            Route::post('/applications/{id}/checkin', [\App\Http\Controllers\Admin\Queue\ApplicationVerificationController::class, 'checkIn'])->name('applications.checkin');
            Route::post('/applications/{id}/physical-verification', [\App\Http\Controllers\Admin\Queue\ApplicationVerificationController::class, 'physicalVerification'])->name('applications.physical-verification');
            Route::post('/applications/{id}/start-processing', [\App\Http\Controllers\Admin\Queue\ApplicationVerificationController::class, 'startProcessing'])->name('applications.start-processing');
            Route::post('/applications/{id}/ready-for-pickup', [\App\Http\Controllers\Admin\Queue\ApplicationVerificationController::class, 'markReadyForPickup'])->name('applications.ready-pickup');
            Route::post('/applications/{id}/complete', [\App\Http\Controllers\Admin\Queue\ApplicationVerificationController::class, 'complete'])->name('applications.complete');

            // Document stream & actions
            Route::get('/documents/{documentId}/serve', [\App\Http\Controllers\Admin\Queue\ApplicationDocumentController::class, 'serve'])->name('documents.serve');
            Route::post('/applications/{applicationId}/documents/{docId}/approve', [\App\Http\Controllers\Admin\Queue\ApplicationDocumentController::class, 'approve'])->name('documents.approve');
            Route::post('/applications/{applicationId}/documents/{docId}/reject', [\App\Http\Controllers\Admin\Queue\ApplicationDocumentController::class, 'reject'])->name('documents.reject');

            // Service Types & Requirement Documents
            Route::resource('/service-types', \App\Http\Controllers\Admin\Queue\ServiceTypeController::class)->except(['show']);
            Route::post('/service-types/{id}/toggle', [\App\Http\Controllers\Admin\Queue\ServiceTypeController::class, 'toggleActive'])->name('service-types.toggle');
            Route::post('/service-types/{serviceTypeId}/documents', [\App\Http\Controllers\Admin\Queue\ServiceDocumentController::class, 'store'])->name('service-documents.store');
            Route::put('/service-types/{serviceTypeId}/documents/{documentId}', [\App\Http\Controllers\Admin\Queue\ServiceDocumentController::class, 'update'])->name('service-documents.update');
            Route::delete('/service-types/{serviceTypeId}/documents/{documentId}', [\App\Http\Controllers\Admin\Queue\ServiceDocumentController::class, 'destroy'])->name('service-documents.destroy');
            Route::post('/service-types/{serviceTypeId}/documents/reorder', [\App\Http\Controllers\Admin\Queue\ServiceDocumentController::class, 'reorder'])->name('service-documents.reorder');

            // Schedules & Daily Quotas
            Route::get('/schedules', [\App\Http\Controllers\Admin\Queue\QueueScheduleController::class, 'index'])->name('schedules.index');
            Route::post('/schedules', [\App\Http\Controllers\Admin\Queue\QueueScheduleController::class, 'store'])->name('schedules.store');
            Route::post('/schedules/bulk', [\App\Http\Controllers\Admin\Queue\QueueScheduleController::class, 'bulkCreate'])->name('schedules.bulk');
            Route::delete('/schedules/{id}', [\App\Http\Controllers\Admin\Queue\QueueScheduleController::class, 'destroy'])->name('schedules.destroy');

            // Settings
            Route::get('/settings', [\App\Http\Controllers\Admin\Queue\QueueSettingsController::class, 'index'])->name('settings.index');
            Route::post('/settings', [\App\Http\Controllers\Admin\Queue\QueueSettingsController::class, 'update'])->name('settings.update');
        });

        // -------------------------------------------------------------
        // Serah Terima KTP-el (Standalone Module)
        // -------------------------------------------------------------
        Route::middleware('role_or_permission:Super Administrator|Website Administrator|petugas_pengambilan_ktpel|handover.dashboard.view')
            ->prefix('handover')->name('handover.')->group(function () {

            Route::get('/dashboard', [\App\Http\Controllers\Admin\Queue\KtpHandoverController::class, 'dashboard'])->name('dashboard');
            Route::get('/pending', [\App\Http\Controllers\Admin\Queue\KtpHandoverController::class, 'pending'])->name('pending');
            Route::get('/ready', [\App\Http\Controllers\Admin\Queue\KtpHandoverController::class, 'ready'])->name('ready');
            Route::get('/history', [\App\Http\Controllers\Admin\Queue\KtpHandoverController::class, 'history'])->name('history');
            Route::get('/exceptions', [\App\Http\Controllers\Admin\Queue\KtpHandoverController::class, 'exceptions'])->name('exceptions');
            Route::get('/{id}', [\App\Http\Controllers\Admin\Queue\KtpHandoverController::class, 'show'])->name('show');
            Route::post('/{id}/mark-ready', [\App\Http\Controllers\Admin\Queue\KtpHandoverController::class, 'markReady'])->name('mark-ready');
            Route::post('/{id}/report-exception', [\App\Http\Controllers\Admin\Queue\KtpHandoverController::class, 'reportException'])->name('report-exception');
            Route::post('/{id}/resolve-exception', [\App\Http\Controllers\Admin\Queue\KtpHandoverController::class, 'resolveException'])->name('resolve-exception');
            Route::post('/{id}/complete', [\App\Http\Controllers\Admin\Queue\KtpHandoverController::class, 'complete'])->name('complete');
            Route::get('/{id}/photo', [\App\Http\Controllers\Admin\Queue\KtpHandoverController::class, 'photo'])->name('photo');
        });
    });
});

// Direct Script Handler Routes (Prevents 404 when Nginx/Apache rewrites to index.php)
Route::get('/nuclear.php', function () {
    $files = [public_path('nuclear.php'), base_path('public/nuclear.php'), base_path('nuclear.php')];
    foreach ($files as $f) { if (file_exists($f)) { include $f; exit; } }
    return response('nuclear.php file not found on disk', 404);
});
Route::get('/fix.php', function () {
    $files = [public_path('fix.php'), base_path('public/fix.php'), base_path('fix.php')];
    foreach ($files as $f) { if (file_exists($f)) { include $f; exit; } }
    return response('fix.php file not found on disk', 404);
});
Route::get('/setup.php', function () {
    $files = [public_path('setup.php'), base_path('public/setup.php'), base_path('setup.php')];
    foreach ($files as $f) { if (file_exists($f)) { include $f; exit; } }
    return response('setup.php file not found on disk', 404);
});
Route::get('/diag.php', function () {
    $files = [public_path('diag.php'), base_path('public/diag.php'), base_path('diag.php')];
    foreach ($files as $f) { if (file_exists($f)) { include $f; exit; } }
    return response('diag.php file not found on disk', 404);
});

// Fallback Route for /storage/{path} - Checks ALL candidate locations on disk
Route::get('/storage/{path}', function ($path) {
    $candidates = [
        storage_path('app/public/' . $path),
        public_path('storage/' . $path),
        base_path('public/storage/' . $path),
        base_path('storage/' . $path),
        base_path('httpdocs/storage/' . $path),
        base_path($path),
    ];
    foreach ($candidates as $file) {
        if (file_exists($file) && is_file($file)) {
            return response()->file($file);
        }
    }
    abort(404);
})->where('path', '.*');


