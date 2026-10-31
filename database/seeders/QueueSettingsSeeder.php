<?php

namespace Database\Seeders;

use App\Models\Queue\QueueSetting;
use Illuminate\Database\Seeder;

class QueueSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'queue.open_days', 'value' => 'monday,tuesday,wednesday,thursday,friday', 'group' => 'schedule', 'description' => 'Hari operasional loket antrian online (dipisahkan koma)'],
            ['key' => 'queue.open_time', 'value' => '08:00', 'group' => 'schedule', 'description' => 'Jam mulai pelayanan loket'],
            ['key' => 'queue.close_time', 'value' => '15:30', 'group' => 'schedule', 'description' => 'Jam selesai pelayanan loket'],
            ['key' => 'queue.registration_open_days_ahead', 'value' => '14', 'group' => 'schedule', 'description' => 'Batas hari ke depan pendaftaran antrian dibuka'],
            ['key' => 'queue.default_daily_quota', 'value' => '70', 'group' => 'quota', 'description' => 'Total kuota harian default seluruh layanan'],
            ['key' => 'queue.allow_cancellation_hours', 'value' => '12', 'group' => 'policy', 'description' => 'Batas jam sebelum waktu jadwal di mana pemohon masih dapat membatalkan'],
            ['key' => 'queue.revision_deadline_days', 'value' => '3', 'group' => 'policy', 'description' => 'Batas hari bagi pemohon untuk memperbaiki dokumen sebelum ditolak otomatis'],
            ['key' => 'queue.notify_on_submit', 'value' => '1', 'group' => 'notification', 'description' => 'Kirim notifikasi email saat pendaftaran berhasil diterima'],
            ['key' => 'queue.notify_on_revision', 'value' => '1', 'group' => 'notification', 'description' => 'Kirim notifikasi email saat dokumen membutuhkan perbaikan'],
            ['key' => 'queue.notify_on_approved', 'value' => '1', 'group' => 'notification', 'description' => 'Kirim notifikasi email saat dokumen disetujui & nomor antrian terbit'],
            ['key' => 'queue.notify_on_rejected', 'value' => '1', 'group' => 'notification', 'description' => 'Kirim notifikasi email saat permohonan ditolak'],
            ['key' => 'queue.office_address', 'value' => 'Jl. Soekarno Hatta No. 9, Dompu - NTB 84211', 'group' => 'general', 'description' => 'Alamat kantor pelayanan untuk instruksi kehadiran'],
            ['key' => 'queue.helpdesk_phone', 'value' => '0823-4000-0000', 'group' => 'general', 'description' => 'Nomor WhatsApp Helpdesk Pelayanan KTP-el'],
        ];

        foreach ($settings as $setting) {
            QueueSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
