<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\CaptchaController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;

// SEO: Sitemap & Robots.txt
Route::get('/sitemap.xml', [\App\Http\Controllers\Public\SitemapController::class, 'sitemap'])->name('public.sitemap');
Route::get('/robots.txt', [\App\Http\Controllers\Public\SitemapController::class, 'robots'])->name('public.robots');

// Public User-Facing routes
Route::get('/', [\App\Http\Controllers\Public\HomeController::class, 'index'])->name('public.home');

// Enterprise Profile Module Public Routes
Route::get('/profil', [\App\Http\Controllers\Public\ProfilePageController::class, 'index'])->name('public.profile.index');
Route::get('/profil/struktur-organisasi', [\App\Http\Controllers\Public\OrganizationChartViewerController::class, 'show'])->name('public.profile.org-chart');
Route::get('/profil/pejabat', [\App\Http\Controllers\Public\OfficialDirectoryController::class, 'index'])->name('public.profile.officials.index');
Route::get('/profil/pejabat/{identifier}', [\App\Http\Controllers\Public\OfficialDirectoryController::class, 'show'])->name('public.profile.officials.show');

// Public Profile REST APIs
Route::prefix('api/v1/profile')->group(function () {
    Route::get('/sections', [\App\Http\Controllers\Api\ProfileApiController::class, 'sections']);
    Route::get('/tree', [\App\Http\Controllers\Api\ProfileApiController::class, 'tree']);
    Route::get('/officials', [\App\Http\Controllers\Api\ProfileApiController::class, 'officials']);
    Route::get('/officials/{identifier}', [\App\Http\Controllers\Api\ProfileApiController::class, 'officialDetail']);
});
Route::get('/page/{slug}', [\App\Http\Controllers\Public\PageController::class, 'show'])->name('public.pages.show');
Route::get('/news', [\App\Http\Controllers\Public\NewsController::class, 'index'])->name('public.news.index');
Route::get('/news/{slug}', [\App\Http\Controllers\Public\NewsController::class, 'show'])->name('public.news.show');
Route::get('/gallery', [\App\Http\Controllers\Public\GalleryController::class, 'index'])->name('public.gallery.index');
Route::get('/gallery/{slug}', [\App\Http\Controllers\Public\GalleryController::class, 'show'])->name('public.gallery.show');
Route::get('/downloads', [\App\Http\Controllers\Public\DownloadCenterController::class, 'index'])->name('public.downloads.index');
Route::get('/downloads/stream/{id}', [\App\Http\Controllers\Public\DownloadController::class, 'stream'])->name('public.downloads.stream')->middleware('signed');
Route::get('/contact', [\App\Http\Controllers\Public\ContactController::class, 'index'])->name('public.contact');
Route::get('/search', [\App\Http\Controllers\Public\SearchController::class, 'search'])->name('public.search');
Route::get('/statistik-kependudukan', [\App\Http\Controllers\Public\DemographicController::class, 'statistics'])->name('public.statistics');
Route::get('/layanan', [\App\Http\Controllers\Public\ServiceRequirementController::class, 'index'])->name('public.services.index');
Route::get('/layanan/survei', [\App\Http\Controllers\Public\PublicSurveyController::class, 'index'])->name('public.survey.index');
Route::post('/layanan/survei', [\App\Http\Controllers\Public\PublicSurveyController::class, 'store'])->name('public.survey.store');
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

// Admin Portal Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });

    // Guest Admin
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login']);
    });

    // Authenticated Admin
    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Enterprise Profile Module CMS
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/dashboard', [\App\Http\Controllers\Admin\Profile\ProfileDashboardController::class, 'index'])->name('dashboard');
            Route::get('/builder', [\App\Http\Controllers\Admin\Profile\ProfileBuilderController::class, 'index'])->name('builder');
            Route::post('/builder/reorder', [\App\Http\Controllers\Admin\Profile\ProfileBuilderController::class, 'reorder'])->name('builder.reorder');
            Route::post('/builder/section/{section}/setting', [\App\Http\Controllers\Admin\Profile\ProfileBuilderController::class, 'updateSetting'])->name('builder.setting.update');

            // Officials Master Directory CRUD
            Route::resource('/officials', \App\Http\Controllers\Admin\Profile\OfficialController::class);

            // Organization Chart Visual Tree Editor
            Route::get('/organization-chart', [\App\Http\Controllers\Admin\Profile\OrganizationChartController::class, 'index'])->name('org-chart.index');
            Route::post('/organization-chart/node', [\App\Http\Controllers\Admin\Profile\OrganizationChartController::class, 'storeNode'])->name('org-chart.node.store');
            Route::put('/organization-chart/node/{node}', [\App\Http\Controllers\Admin\Profile\OrganizationChartController::class, 'updateNode'])->name('org-chart.node.update');
            Route::delete('/organization-chart/node/{node}', [\App\Http\Controllers\Admin\Profile\OrganizationChartController::class, 'destroyNode'])->name('org-chart.node.destroy');
        });

        // User Management CMS
        Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
        Route::post('/users', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');

        // Theme Customizer CMS
        Route::get('/theme', [\App\Http\Controllers\Admin\ThemeController::class, 'index'])->name('theme.index');
        Route::post('/theme', [\App\Http\Controllers\Admin\ThemeController::class, 'update'])->name('theme.update');

        // File Manager CMS
        Route::get('/files', [\App\Http\Controllers\Admin\FileManagerController::class, 'index'])->name('files.index');
        Route::post('/files/folder', [\App\Http\Controllers\Admin\FileManagerController::class, 'createFolder'])->name('files.folder.create');
        Route::post('/files/upload', [\App\Http\Controllers\Admin\FileManagerController::class, 'uploadFile'])->name('files.upload');
        Route::post('/files/{file}/version', [\App\Http\Controllers\Admin\FileManagerController::class, 'uploadVersion'])->name('files.version.upload');
        Route::delete('/files/{file}', [\App\Http\Controllers\Admin\FileManagerController::class, 'destroyFile'])->name('files.destroy');
        Route::delete('/files/folder/{folder}', [\App\Http\Controllers\Admin\FileManagerController::class, 'destroyFolder'])->name('files.folder.destroy');

        // Audit Logs CMS
        Route::get('/audit-logs', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('audit-logs.index');

        // Notifications CMS (JSON APIs)
        Route::get('/notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{id}/read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [\App\Http\Controllers\Admin\NotificationController::class, 'markAllRead'])->name('notifications.read-all');

        // API Key Management CMS
        Route::get('/api-keys', [\App\Http\Controllers\Admin\ApiKeyController::class, 'index'])->name('api-keys.index');
        Route::post('/api-keys', [\App\Http\Controllers\Admin\ApiKeyController::class, 'store'])->name('api-keys.store');
        Route::post('/api-keys/{apiKey}/regenerate', [\App\Http\Controllers\Admin\ApiKeyController::class, 'regenerate'])->name('api-keys.regenerate');
        Route::post('/api-keys/{apiKey}/toggle', [\App\Http\Controllers\Admin\ApiKeyController::class, 'toggleStatus'])->name('api-keys.toggle');
        Route::delete('/api-keys/{apiKey}', [\App\Http\Controllers\Admin\ApiKeyController::class, 'destroy'])->name('api-keys.destroy');

        // API Monitor Dashboard
        Route::get('/api-dashboard', [\App\Http\Controllers\Admin\ApiDashboardController::class, 'index'])->name('api-dashboard.index');

        // API Settings CMS
        Route::get('/api-settings', [\App\Http\Controllers\Admin\ApiSettingController::class, 'index'])->name('api-settings.index');
        Route::post('/api-settings', [\App\Http\Controllers\Admin\ApiSettingController::class, 'update'])->name('api-settings.update');

        // External Services (SANAI Online) CMS
        Route::get('/external-services', [\App\Http\Controllers\Admin\ExternalServiceController::class, 'index'])->name('external-services.index');
        Route::post('/external-services', [\App\Http\Controllers\Admin\ExternalServiceController::class, 'update'])->name('external-services.update');

        // Office Contact & Location CMS
        Route::get('/office-contact', [\App\Http\Controllers\Admin\OfficeContactController::class, 'index'])->name('office-contact.index');
        Route::post('/office-contact', [\App\Http\Controllers\Admin\OfficeContactController::class, 'update'])->name('office-contact.update');

        // Dynamic Pages CMS
        Route::resource('/pages', \App\Http\Controllers\Admin\PageController::class)->except(['show']);

        // News CMS
        Route::get('/news/categories', [\App\Http\Controllers\Admin\NewsController::class, 'categories'])->name('news.categories');
        Route::post('/news/categories', [\App\Http\Controllers\Admin\NewsController::class, 'storeCategory'])->name('news.categories.store');
        Route::put('/news/categories/{newsCategory}', [\App\Http\Controllers\Admin\NewsController::class, 'updateCategory'])->name('news.categories.update');
        Route::delete('/news/categories/{newsCategory}', [\App\Http\Controllers\Admin\NewsController::class, 'destroyCategory'])->name('news.categories.destroy');
        Route::post('/news/{id}/restore', [\App\Http\Controllers\Admin\NewsController::class, 'restore'])->name('news.restore');
        Route::post('/news/upload-image', [\App\Http\Controllers\Admin\NewsController::class, 'uploadInlineImage'])->name('news.upload-image');
        Route::resource('/news', \App\Http\Controllers\Admin\NewsController::class)->except(['show']);

        // Announcements CMS
        Route::resource('/announcements', \App\Http\Controllers\Admin\AnnouncementController::class)->only(['index', 'store', 'update', 'destroy']);

        // Banner CMS
        Route::resource('/banners', \App\Http\Controllers\Admin\BannerController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::post('/banners/reorder', [\App\Http\Controllers\Admin\BannerController::class, 'reorder'])->name('banners.reorder');

        // Gallery CMS
        Route::get('/gallery', [\App\Http\Controllers\Admin\GalleryController::class, 'index'])->name('gallery.index');
        Route::get('/gallery/{galleryAlbum}', [\App\Http\Controllers\Admin\GalleryController::class, 'show'])->name('gallery.show');
        Route::post('/gallery/album', [\App\Http\Controllers\Admin\GalleryController::class, 'storeAlbum'])->name('gallery.album.store');
        Route::put('/gallery/album/{galleryAlbum}', [\App\Http\Controllers\Admin\GalleryController::class, 'updateAlbum'])->name('gallery.album.update');
        Route::delete('/gallery/album/{galleryAlbum}', [\App\Http\Controllers\Admin\GalleryController::class, 'destroyAlbum'])->name('gallery.album.destroy');
        Route::post('/gallery/{galleryAlbum}/upload', [\App\Http\Controllers\Admin\GalleryController::class, 'uploadItem'])->name('gallery.item.upload');
        Route::delete('/gallery/item/{galleryItem}', [\App\Http\Controllers\Admin\GalleryController::class, 'destroyItem'])->name('gallery.item.destroy');

        // Downloads CMS
        Route::resource('/downloads', \App\Http\Controllers\Admin\DownloadController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::post('/downloads/categories', [\App\Http\Controllers\Admin\DownloadController::class, 'storeCategory'])->name('downloads.categories.store');
        Route::delete('/downloads/categories/{downloadCategory}', [\App\Http\Controllers\Admin\DownloadController::class, 'destroyCategory'])->name('downloads.categories.destroy');

        // FAQs CMS
        Route::resource('/faqs', \App\Http\Controllers\Admin\FaqController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::post('/faqs/reorder', [\App\Http\Controllers\Admin\FaqController::class, 'reorder'])->name('faqs.reorder');

        // Menus CMS
        Route::get('/menus', [\App\Http\Controllers\Admin\MenuController::class, 'index'])->name('menus.index');
        Route::post('/menus', [\App\Http\Controllers\Admin\MenuController::class, 'store'])->name('menus.store');
        Route::delete('/menus/{menu}', [\App\Http\Controllers\Admin\MenuController::class, 'destroy'])->name('menus.destroy');
        Route::post('/menus/{menu}/item', [\App\Http\Controllers\Admin\MenuController::class, 'storeItem'])->name('menus.item.store');
        Route::put('/menus/item/{menuItem}', [\App\Http\Controllers\Admin\MenuController::class, 'updateItem'])->name('menus.item.update');
        Route::delete('/menus/item/{menuItem}', [\App\Http\Controllers\Admin\MenuController::class, 'destroyItem'])->name('menus.item.destroy');
        Route::post('/menus/{menu}/reorder', [\App\Http\Controllers\Admin\MenuController::class, 'reorderItems'])->name('menus.reorder');

        // Demographic CMS
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
        Route::get('/demographics/dashboard', [\App\Http\Controllers\Admin\DemographicController::class, 'dashboard'])->name('demographics.dashboard');

        // Complaint System
        Route::get('/complaints', [\App\Http\Controllers\Admin\ComplaintController::class, 'index'])->name('complaints.index');
        Route::get('/complaints/categories', [\App\Http\Controllers\Admin\ComplaintController::class, 'categories'])->name('complaints.categories');
        Route::post('/complaints/categories', [\App\Http\Controllers\Admin\ComplaintController::class, 'storeCategory'])->name('complaints.categories.store');
        Route::put('/complaints/categories/{category}', [\App\Http\Controllers\Admin\ComplaintController::class, 'updateCategory'])->name('complaints.categories.update');
        Route::delete('/complaints/categories/{category}', [\App\Http\Controllers\Admin\ComplaintController::class, 'destroyCategory'])->name('complaints.categories.destroy');
        Route::get('/complaints/{complaint}', [\App\Http\Controllers\Admin\ComplaintController::class, 'show'])->name('complaints.show');
        Route::post('/complaints/{complaint}/reply', [\App\Http\Controllers\Admin\ComplaintController::class, 'reply'])->name('complaints.reply');
        Route::post('/complaints/{complaint}/status', [\App\Http\Controllers\Admin\ComplaintController::class, 'changeStatus'])->name('complaints.status');
        Route::post('/complaints/{complaint}/assign', [\App\Http\Controllers\Admin\ComplaintController::class, 'assign'])->name('complaints.assign');

        // Service Requirements CMS
        Route::resource('/service-requirements', \App\Http\Controllers\Admin\ServiceRequirementController::class)->except(['show']);

        // Innovations CMS
        Route::resource('/innovations', \App\Http\Controllers\Admin\InnovationController::class)->except(['show']);

        // Survei Kepuasan Masyarakat (IKM) CMS
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
        });

        // PPID Management
        Route::prefix('ppid')->name('ppid.')->group(function () {
            // Pages
            Route::get('/pages', [\App\Http\Controllers\Admin\PpidController::class, 'pagesIndex'])->name('pages.index');
            Route::get('/pages/{page}/edit', [\App\Http\Controllers\Admin\PpidController::class, 'pagesEdit'])->name('pages.edit');
            Route::put('/pages/{page}', [\App\Http\Controllers\Admin\PpidController::class, 'pagesUpdate'])->name('pages.update');
            // Documents
            Route::get('/documents', [\App\Http\Controllers\Admin\PpidController::class, 'documentsIndex'])->name('documents.index');
            Route::get('/documents/create', [\App\Http\Controllers\Admin\PpidController::class, 'documentsCreate'])->name('documents.create');
            Route::post('/documents', [\App\Http\Controllers\Admin\PpidController::class, 'documentsStore'])->name('documents.store');
            Route::get('/documents/{document}/edit', [\App\Http\Controllers\Admin\PpidController::class, 'documentsEdit'])->name('documents.edit');
            Route::put('/documents/{document}', [\App\Http\Controllers\Admin\PpidController::class, 'documentsUpdate'])->name('documents.update');
            Route::delete('/documents/{document}', [\App\Http\Controllers\Admin\PpidController::class, 'documentsDestroy'])->name('documents.destroy');
            // Requests (Permohonan Informasi)
            Route::get('/requests', [\App\Http\Controllers\Admin\PpidController::class, 'requestsIndex'])->name('requests.index');
            Route::get('/requests/{request}', [\App\Http\Controllers\Admin\PpidController::class, 'requestsShow'])->name('requests.show');
            Route::post('/requests/{ppidRequest}/respond', [\App\Http\Controllers\Admin\PpidController::class, 'requestsRespond'])->name('requests.respond');
        });
    });
});
