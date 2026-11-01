<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Archive Packages Table
        Schema::create('archive_packages', function (Blueprint $table) {
            $table->id();
            $table->string('package_code', 100)->unique();
            $table->string('archive_period', 20)->nullable()->index();
            $table->foreignId('service_type_id')->nullable()->constrained('queue_service_types')->nullOnDelete();
            $table->json('filter_criteria')->nullable();
            $table->string('status', 40)->default('READY_FOR_DOWNLOAD')->index();
            $table->unsignedInteger('total_applications')->default(0);
            $table->unsignedInteger('total_files')->default(0);
            $table->unsignedBigInteger('total_uncompressed_bytes')->default(0);
            $table->unsignedBigInteger('package_size_bytes')->default(0);
            $table->string('sha256_checksum', 64)->nullable()->index();
            $table->string('zip_path', 500)->nullable();
            $table->string('disk', 50)->default('local');
            $table->longText('manifest_data')->nullable();
            $table->text('error_message')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // Operator confirmation (Manual download & storage on offline media)
            $table->boolean('is_operator_confirmed')->default(false)->index();
            $table->timestamp('operator_confirmed_at')->nullable();
            $table->foreignId('operator_confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('operator_storage_location_notes')->nullable();

            // Permanent storage integration tracking
            $table->string('permanent_storage_status', 50)->default('NOT_CONFIGURED')->index();
            $table->string('permanent_storage_destination', 500)->nullable();
            $table->timestamp('permanent_storage_verified_at')->nullable();
            $table->string('permanent_storage_checksum', 64)->nullable();

            // Source cleaning protection (Strictly false in initial implementation)
            $table->boolean('source_files_cleaned')->default(false)->index();
            $table->timestamp('source_files_cleaned_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Archive Package Items (Pivot/Application Items)
        Schema::create('archive_package_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('archive_package_id')->constrained('archive_packages')->cascadeOnDelete();
            $table->foreignId('application_id')->constrained('queue_applications')->cascadeOnDelete();
            $table->string('reg_number', 50)->index();
            $table->string('applicant_name_masked', 150)->nullable();
            $table->unsignedInteger('document_count')->default(0);
            $table->unsignedBigInteger('total_size_bytes')->default(0);
            $table->string('status', 30)->default('INCLUDED')->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['archive_package_id', 'application_id']);
        });

        // 3. Archive Audit Logs
        Schema::create('archive_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('archive_package_id')->nullable()->constrained('archive_packages')->nullOnDelete();
            $table->foreignId('application_id')->nullable()->constrained('queue_applications')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 60)->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('details')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 4. Archive Settings Table
        Schema::create('archive_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->longText('value')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archive_settings');
        Schema::dropIfExists('archive_audit_logs');
        Schema::dropIfExists('archive_package_items');
        Schema::dropIfExists('archive_packages');
    }
};
