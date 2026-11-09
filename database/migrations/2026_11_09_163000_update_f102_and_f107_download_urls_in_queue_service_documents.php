<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Download;

return new class extends Migration
{
    public function up(): void
    {
        $f102 = Download::where(fn($q) => $q->where('title', 'like', '%F1.02%')->orWhere('title', 'like', '%F-1.02%'))->first();
        $f102Url = $f102 ? "/downloads/stream/{$f102->id}" : '/downloads/stream/19';

        $f107 = Download::where(fn($q) => $q->where('title', 'like', '%F1.07%')->orWhere('title', 'like', '%F-1.07%'))
            ->where('title', 'like', '%kuasa%')
            ->first();
        if (!$f107) {
            $f107 = Download::where('title', 'like', '%kuasa%')->first();
        }
        $f107Url = $f107 ? "/downloads/stream/{$f107->id}" : '/downloads/stream/24';

        DB::table('queue_service_documents')
            ->where(function($q) {
                $q->where('label', 'like', '%F-1.02%')->orWhere('label', 'like', '%F1.02%');
            })
            ->update(['template_url' => $f102Url]);

        DB::table('queue_service_documents')
            ->where(function($q) {
                $q->where('label', 'like', '%F-1.07%')
                  ->orWhere('label', 'like', '%F1.07%')
                  ->orWhere('label', 'like', '%kuasa%');
            })
            ->update(['template_url' => $f107Url]);
    }

    public function down(): void
    {
        // Safe no-op
    }
};
