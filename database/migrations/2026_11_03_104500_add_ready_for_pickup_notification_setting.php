<?php

use App\Models\Queue\QueueSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        QueueSetting::updateOrCreate(
            ['key' => 'queue.notify_on_ready_for_pickup'],
            [
                'value'       => '1',
                'group'       => 'notification',
                'description' => 'Kirim notifikasi email kepada pemohon saat fisik KTP-el selesai dicetak dan siap diambil di loket penyerahan.',
            ]
        );
    }

    public function down(): void
    {
        QueueSetting::where('key', 'queue.notify_on_ready_for_pickup')->delete();
    }
};
