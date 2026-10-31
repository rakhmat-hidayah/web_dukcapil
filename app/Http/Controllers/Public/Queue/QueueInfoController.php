<?php

namespace App\Http\Controllers\Public\Queue;

use App\Http\Controllers\Controller;
use App\Models\Queue\QueueApplication;
use App\Models\Queue\QueueSchedule;
use App\Models\Queue\QueueSetting;
use App\Models\Queue\QueueSlot;
use App\Models\Queue\ServiceType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QueueInfoController extends Controller
{
    /**
     * Display the online queue public portal / landing page.
     */
    public function index(): Response
    {
        // Auto-seed initial queue services & settings if table is empty in production
        if (ServiceType::count() === 0) {
            try {
                (new \Database\Seeders\QueueServiceTypeSeeder())->run();
                (new \Database\Seeders\QueueSettingsSeeder())->run();
                (new \Database\Seeders\QueueScheduleSeeder())->run();
            } catch (\Throwable $e) {
                \Log::warning('Queue auto-seed error: ' . $e->getMessage());
            }
        }

        $today = Carbon::today()->format('Y-m-d');

        // Fetch active service types with their requirement counts
        $services = ServiceType::active()
            ->withCount(['documents', 'requiredDocuments'])
            ->with(['documents' => function ($q) {
                $q->orderBy('sort_order');
            }])
            ->get();

        // Today's schedule and quota summary
        $todaySchedule = QueueSchedule::where('schedule_date', $today)->first();
        $todaySlots = $todaySchedule ? QueueSlot::with('serviceType')->where('schedule_id', $todaySchedule->id)->get() : collect();

        $todayStats = [
            'is_open' => $todaySchedule ? $todaySchedule->isAvailable() : false,
            'is_holiday' => $todaySchedule ? $todaySchedule->is_holiday : false,
            'holiday_note' => $todaySchedule ? $todaySchedule->holiday_note : null,
            'open_time' => $todaySchedule ? substr($todaySchedule->open_time, 0, 5) : '08:00',
            'close_time' => $todaySchedule ? substr($todaySchedule->close_time, 0, 5) : '15:30',
            'total_quota' => $todaySlots->sum('quota'),
            'total_booked' => $todaySlots->sum('booked_count'),
            'total_remaining' => max(0, $todaySlots->sum('quota') - $todaySlots->sum('booked_count')),
        ];

        // Next 7 business days availability overview
        $upcomingSchedules = QueueSchedule::with(['slots.serviceType'])
            ->where('schedule_date', '>=', $today)
            ->where('is_open', true)
            ->where('is_holiday', false)
            ->orderBy('schedule_date')
            ->limit(7)
            ->get()
            ->map(fn($s) => [
                'date' => $s->schedule_date->format('Y-m-d'),
                'date_label' => $s->schedule_date->translatedFormat('D, d M'),
                'total_quota' => $s->slots->sum('quota'),
                'total_booked' => $s->slots->sum('booked_count'),
                'remaining' => max(0, $s->slots->sum('quota') - $s->slots->sum('booked_count')),
            ]);

        $settings = [
            'office_address' => QueueSetting::get('queue.office_address', 'Jl. Bhayangkara No. 01, Kel. Bada, Kec. Dompu, NTB'),
            'helpdesk_phone' => QueueSetting::get('queue.helpdesk_phone', '0823-4000-0000'),
            'open_time' => QueueSetting::get('queue.open_time', '08:00'),
            'close_time' => QueueSetting::get('queue.close_time', '15:30'),
        ];

        return Inertia::render('Public/Queue/Index', [
            'services' => $services,
            'todayStats' => $todayStats,
            'upcomingSchedules' => $upcomingSchedules,
            'settings' => $settings,
        ]);
    }

    /**
     * Search application status by Registration Number or NIK.
     * Directs NIK searches securely to Applicant History verification.
     */
    public function checkStatus(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:4|max:30',
        ], [
            'query.required' => 'Masukkan Nomor Registrasi atau NIK Anda.',
            'query.min' => 'Pencarian minimal 4 karakter.',
        ]);

        $query = trim($request->input('query'));
        $digitsOnly = preg_replace('/[^0-9]/', '', $query);

        // Case A: User entered a 16-digit NIK -> Direct to secure history verification
        if (strlen($digitsOnly) === 16) {
            $verifiedNik = $request->session()->get('applicant_verified_nik');
            if ($verifiedNik === $digitsOnly) {
                return redirect()->route('queue.history');
            }

            $hasApp = QueueApplication::where('applicant_nik', $digitsOnly)->exists();
            if (!$hasApp) {
                return back()->withErrors([
                    'query' => 'Tidak ditemukan permohonan dengan NIK tersebut. Pastikan NIK benar atau silakan ajukan pendaftaran baru.',
                ])->withInput();
            }

            return redirect()->route('queue.history', ['nik' => $digitsOnly])
                ->with('info', 'Silakan masukkan nomor WhatsApp/HP terdaftar untuk memverifikasi dan melihat seluruh riwayat permohonan Anda.');
        }

        // Case B: Search by Registration Number (e.g. REG-2610-XXXXXX)
        $application = QueueApplication::where('reg_number', strtoupper($query))
            ->orWhere('reg_number', $query)
            ->latest()
            ->first();

        if (!$application) {
            return back()->withErrors([
                'query' => 'Pengajuan tidak ditemukan. Pastikan Nomor Registrasi atau NIK yang Anda masukkan sudah benar.',
            ])->withInput();
        }

        // Establish session so user can access their other applications
        $request->session()->put('applicant_verified_nik', $application->applicant_nik);
        $request->session()->put('applicant_verified_at', now()->timestamp);

        return redirect()->route('queue.status', ['token' => $application->access_token]);
    }
}
