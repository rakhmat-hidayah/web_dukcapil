<?php

use App\Models\Queue\ServiceDocument;
use App\Models\Queue\ServiceType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('queue_service_documents') || !Schema::hasTable('queue_service_types')) {
            return;
        }

        $service = ServiceType::where('slug', 'perubahan-elemen-data')->first();
        if ($service) {
            $f106Docs = ServiceDocument::where('service_type_id', $service->id)
                ->where(function ($q) {
                    $q->where('label', 'like', '%F-1.06%')
                      ->orWhere('label', 'like', '%F1.06%')
                      ->orWhere('label', 'like', '%Perubahan Elemen Data%');
                })
                ->get();

            foreach ($f106Docs as $doc) {
                try {
                    $hasUploads = DB::table('queue_application_documents')
                        ->where('service_document_id', $doc->id)
                        ->exists();
                    if (!$hasUploads) {
                        $doc->delete();
                    }
                } catch (\Throwable) {
                    // Safe ignore
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: requirements are fully dynamic and managed via Admin CMS
    }
};
