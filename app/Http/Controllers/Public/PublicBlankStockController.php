<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\BlankStock\KtpBlankSetting;
use App\Services\BlankStock\BlankStockService;
use Inertia\Inertia;
use Inertia\Response;

class PublicBlankStockController extends Controller
{
    public function __construct(
        protected BlankStockService $stockService
    ) {}

    public function index(): Response
    {
        $summary = $this->stockService->getPublicStockSummary();
        $usageReports = $this->stockService->getPublicUsageReports(15);

        // Safe SEO Metadata
        $seo = [
            'title'       => 'Informasi Ketersediaan Blangko KTP-el - Disdukcapil Kabupaten Dompu',
            'description' => 'Informasi resmi real-time ketersediaan dan persediaan blangko KTP-el di Dinas Kependudukan dan Pencatatan Sipil Kabupaten Dompu.',
        ];

        return Inertia::render('Public/BlankStock/Index', [
            'summary'      => $summary,
            'usageReports' => $usageReports,
            'seo'          => $seo,
        ]);
    }
}
