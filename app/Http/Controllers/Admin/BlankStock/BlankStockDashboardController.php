<?php

namespace App\Http\Controllers\Admin\BlankStock;

use App\Http\Controllers\Controller;
use App\Services\BlankStock\BlankStockService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlankStockDashboardController extends Controller
{
    public function __construct(
        protected BlankStockService $stockService
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('blank_stocks.dashboard');

        $summary = $this->stockService->getDashboardSummary();

        return Inertia::render('Admin/BlankStock/Dashboard', [
            'summary' => $summary,
        ]);
    }
}
