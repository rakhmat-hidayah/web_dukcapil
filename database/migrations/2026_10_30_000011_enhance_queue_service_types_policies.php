<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('queue_service_types', function (Blueprint $table) {
            $table->boolean('allow_self')->default(true)->after('requires_face_photo');
            $table->boolean('allow_family_in_kk')->default(true)->after('allow_self');
            $table->boolean('allow_family_out_kk')->default(false)->after('allow_family_in_kk');
            $table->boolean('allow_other')->default(false)->after('allow_family_out_kk');
            $table->boolean('require_power_of_attorney')->default(false)->after('allow_other');
            $table->boolean('require_absence_reason')->default(true)->after('require_power_of_attorney');
            $table->boolean('is_biometric_first_recording')->default(false)->after('require_absence_reason');
            $table->text('policy_notes')->nullable()->after('is_biometric_first_recording');
        });

        Schema::table('queue_service_documents', function (Blueprint $table) {
            $table->string('applicable_to', 30)->default('all')->after('capture_mode')
                ->comment('all, self_only, representative_only');
        });
    }

    public function down(): void
    {
        Schema::table('queue_service_types', function (Blueprint $table) {
            $table->dropColumn([
                'allow_self',
                'allow_family_in_kk',
                'allow_family_out_kk',
                'allow_other',
                'require_power_of_attorney',
                'require_absence_reason',
                'is_biometric_first_recording',
                'policy_notes',
            ]);
        });

        Schema::table('queue_service_documents', function (Blueprint $table) {
            $table->dropColumn('applicable_to');
        });
    }
};
