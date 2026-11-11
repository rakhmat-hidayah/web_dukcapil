<?php

namespace App\Http\Controllers\Public\Queue;

use App\Http\Controllers\Controller;
use App\Models\Queue\QueueApplication;
use App\Models\Queue\QueueSchedule;
use App\Models\Queue\QueueSetting;
use App\Models\Queue\QueueSlot;
use App\Models\Queue\ServiceType;
use App\Services\ConfigurationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QueueInfoController extends Controller
{
    /**
     * Ensure database migrations are executed and schema is up-to-date.
     */
    public static function ensureDatabaseSchema(): void
    {
        static $checked = false;
        if ($checked) return;
        $checked = true;

        try {
            $needsMigration = false;

            if (\Illuminate\Support\Facades\Schema::hasTable('queue_service_documents') &&
                !\Illuminate\Support\Facades\Schema::hasColumn('queue_service_documents', 'is_active')) {
                $needsMigration = true;
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('queue_applications')) {
                if (\Illuminate\Support\Facades\Schema::hasColumn('queue_applications', 'applicant_phone') ||
                    \Illuminate\Support\Facades\Schema::hasColumn('queue_applications', 'representative_phone') ||
                    \Illuminate\Support\Facades\Schema::hasColumn('queue_applications', 'applicant_address') ||
                    !\Illuminate\Support\Facades\Schema::hasColumn('queue_applications', 'family_shdk')) {
                    $needsMigration = true;
                }
            }

            if ($needsMigration) {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            }

            // Direct DDL fallback if artisan migrate was blocked or did not drop columns
            if (\Illuminate\Support\Facades\Schema::hasTable('queue_applications')) {
                $legacyCols = [
                    'applicant_phone',
                    'applicant_address',
                    'applicant_village',
                    'applicant_district',
                    'applicant_birth_place',
                    'applicant_birth_date',
                    'representative_phone',
                ];
                $toDrop = [];
                foreach ($legacyCols as $col) {
                    if (\Illuminate\Support\Facades\Schema::hasColumn('queue_applications', $col)) {
                        $toDrop[] = $col;
                    }
                }
                if (!empty($toDrop)) {
                    \Illuminate\Support\Facades\Schema::table('queue_applications', function (\Illuminate\Database\Schema\Blueprint $table) use ($toDrop) {
                        $table->dropColumn($toDrop);
                    });
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Queue auto-migrate/schema sync warning: ' . $e->getMessage());
        }
    }

    /**
     * Display the online queue public portal / landing page.
     */
    public function index(): Response
    {
        self::ensureDatabaseSchema();

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
        $hasIsActive = \Illuminate\Support\Facades\Schema::hasColumn('queue_service_documents', 'is_active');

        // Fetch active service types with their requirement counts (filtering out automated test artifacts)
        $services = ServiceType::active()
            ->where('slug', 'not like', '%test%')
            ->where('name', 'not like', '%Test%')
            ->orderBy('id', 'asc')
            ->withCount([
                'documents as documents_count' => fn($q) => $hasIsActive ? $q->where('is_active', true) : $q,
                'requiredDocuments'
            ])
            ->with(['documents' => function ($q) use ($hasIsActive) {
                if ($hasIsActive) {
                    $q->where('is_active', true);
                }
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
            'open_time' => $todaySchedule ? substr((string)$todaySchedule->open_time, 0, 5) : '08:00',
            'close_time' => $todaySchedule ? substr((string)$todaySchedule->close_time, 0, 5) : '15:30',
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
            ->map(function ($s) use ($today) {
                $dateObj = $s->schedule_date instanceof \Carbon\CarbonInterface 
                    ? $s->schedule_date 
                    : ($s->schedule_date ? \Carbon\Carbon::parse($s->schedule_date) : \Carbon\Carbon::parse($today));
                $slots = $s->slots ?? collect();
                return [
                    'date' => $dateObj->format('Y-m-d'),
                    'date_label' => $dateObj->locale('id')->translatedFormat('D, d M'),
                    'total_quota' => $slots->sum('quota'),
                    'total_booked' => $slots->sum('booked_count'),
                    'remaining' => max(0, $slots->sum('quota') - $slots->sum('booked_count')),
                ];
            });

        $settings = [
            'office_address' => ConfigurationService::get('office_address') ?: QueueSetting::get('queue.office_address', 'Jl. Soekarno-Hatta No. 09 Dompu, NTB 84211'),
            'helpdesk_phone' => ConfigurationService::get('whatsapp_number') ?: QueueSetting::get('queue.helpdesk_phone', '6282124824114'),
            'open_time' => QueueSetting::get('queue.open_time', '08:00'),
            'close_time' => QueueSetting::get('queue.close_time', '15:30'),
        ];

        $maintenance = [
            'is_active' => in_array((string) QueueSetting::get('queue.maintenance_mode', '0'), ['1', 'true'], true),
            'title' => QueueSetting::get('queue.maintenance_title', 'Layanan Pendaftaran Online KTP-el Sedang Dalam Pemeliharaan Sistem'),
            'message' => QueueSetting::get('queue.maintenance_message', 'Mohon maaf, saat ini pendaftaran dan antrian online KTP-el Dinas Dukcapil Kabupaten Dompu sedang dinonaktifkan sementara untuk pemeliharaan sistem.'),
            'estimated_end' => QueueSetting::get('queue.maintenance_estimated_end', ''),
        ];

        // Active banners for carousel slider
        $banners = \App\Models\Banner::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        // Portal layout mode with instant rollback support via database or query parameter (?mode=classic / ?mode=app)
        $portalLayout = QueueSetting::get('queue.portal_layout', 'app_portal');
        $queryMode = request()->query('mode');
        if ($queryMode === 'classic') {
            $portalLayout = 'classic';
        } elseif ($queryMode === 'app' || $queryMode === 'modern') {
            $portalLayout = 'app_portal';
        }

        $component = ($portalLayout === 'classic') ? 'Public/Queue/IndexClassic' : 'Public/Queue/Index';

        return Inertia::render($component, [
            'services' => $services,
            'todayStats' => $todayStats,
            'upcomingSchedules' => $upcomingSchedules,
            'settings' => $settings,
            'maintenance' => $maintenance,
            'banners' => $banners,
            'portalLayout' => $portalLayout,
        ]);
    }

    /**
     * Search application status by Registration Number or NIK.
     * Enforces applicant authentication to protect citizen privacy and application data.
     */
    public function checkStatus(Request $request)
    {
        // 1. Enforce applicant authentication
        if (!\Illuminate\Support\Facades\Auth::guard('applicant')->check()) {
            return redirect()->route('applicant.login')
                ->with('warning', 'Silakan masuk ke Akun Pemohon terlebih dahulu untuk melacak status permohonan Anda.');
        }

        /** @var \App\Models\Applicant\ApplicantAccount $applicant */
        $applicant = \Illuminate\Support\Facades\Auth::guard('applicant')->user();
        $rawNik = $applicant->nik;

        $request->validate([
            'query' => 'required|string|min:4|max:30',
        ], [
            'query.required' => 'Masukkan Nomor Registrasi permohonan Anda.',
            'query.min' => 'Pencarian minimal 4 karakter.',
        ]);

        $query = trim($request->input('query'));
        $digitsOnly = preg_replace('/[^0-9]/', '', $query);

        // If user entered 16 digits NIK -> Direct to their applicant dashboard
        if (strlen($digitsOnly) === 16) {
            return redirect()->route('applicant.dashboard');
        }

        // Search application belonging strictly to this applicant account / NIK
        $application = QueueApplication::query()
            ->where(function ($q) use ($applicant, $rawNik) {
                $q->where('applicant_account_id', $applicant->id);
                if ($rawNik) {
                    $q->orWhere('applicant_nik', $rawNik);
                }
            })
            ->where(function ($q) use ($query) {
                $q->where('reg_number', strtoupper($query))
                  ->orWhere('reg_number', $query);
            })
            ->latest()
            ->first();

        if (!$application) {
            return back()->withErrors([
                'query' => 'Permohonan tidak ditemukan pada akun Anda. Pastikan Nomor Registrasi sudah benar.',
            ])->withInput();
        }

        // Auto-link account id if not yet linked
        if (!$application->applicant_account_id) {
            $application->applicant_account_id = $applicant->id;
            $application->save();
        }

        return redirect()->route('queue.status', ['token' => $application->access_token]);
    }
}
