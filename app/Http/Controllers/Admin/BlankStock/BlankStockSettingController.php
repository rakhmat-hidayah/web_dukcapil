<?php

namespace App\Http\Controllers\Admin\BlankStock;

use App\Http\Controllers\Controller;
use App\Models\BlankStock\KtpBlankSetting;
use App\Models\BlankStock\KtpBlankStock;
use App\Services\ActivityLogger;
use App\Services\BlankStock\BlankStockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlankStockSettingController extends Controller
{
    public function __construct(
        protected BlankStockService $stockService
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('blank_stocks.settings.manage');

        $stock = $this->stockService->getAuthoritativeStock();
        $settings = KtpBlankSetting::getAll();

        return Inertia::render('Admin/BlankStock/Settings/Index', [
            'stock'    => [
                'current_stock'        => $stock->current_stock,
                'min_stock_alert'      => $stock->min_stock_alert,
                'critical_stock_alert' => $stock->critical_stock_alert,
            ],
            'settings' => $settings,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorize('blank_stocks.settings.manage');

        $data = $request->validate([
            'min_stock_alert'          => 'required|integer|min:1',
            'critical_stock_alert'     => 'required|integer|min:0',
            'stock_threshold_limited'  => 'required|integer|min:1',
            'stock_threshold_empty'    => 'required|integer|min:0',
            'public_info_enabled'      => 'required|boolean',
            'require_damage_proof'     => 'required|boolean',
            'allow_operator_draft'     => 'required|boolean',
            'notify_on_low_stock'      => 'required|boolean',
        ]);

        $stock = $this->stockService->getAuthoritativeStock();
        $stock->update([
            'min_stock_alert'      => $data['min_stock_alert'],
            'critical_stock_alert' => $data['critical_stock_alert'],
            'updated_by'           => $request->user()->id,
        ]);

        KtpBlankSetting::set('stock_threshold_limited', $data['stock_threshold_limited'], 'Batas minimum persediaan untuk status Terbatas');
        KtpBlankSetting::set('stock_threshold_empty', $data['stock_threshold_empty'], 'Batas stok untuk status Kosong');
        KtpBlankSetting::set('public_info_enabled', $data['public_info_enabled'] ? '1' : '0', 'Status aktif publikasi ketersediaan blangko');
        KtpBlankSetting::set('require_damage_proof', $data['require_damage_proof'] ? '1' : '0', 'Wajib mengunggah bukti foto dokumen blangko rusak');
        KtpBlankSetting::set('allow_operator_draft', $data['allow_operator_draft'] ? '1' : '0', 'Izinkan operator menyimpan laporan sebagai draf');
        KtpBlankSetting::set('notify_on_low_stock', $data['notify_on_low_stock'] ? '1' : '0', 'Notifikasi jika stok menipis');

        KtpBlankSetting::clearCache();

        try {
            ActivityLogger::log("Memperbarui konfigurasi batas stok & publikasi blangko KTP-el", null, 'blank_stock_settings_update');
        } catch (\Throwable $e) {}

        return redirect()->back()->with('success', 'Pengaturan batas persediaan blangko KTP-el berhasil diperbarui.');
    }
}
