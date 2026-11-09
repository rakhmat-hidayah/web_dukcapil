<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('queue_selfie_verifications')) {
            Schema::create('queue_selfie_verifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('application_id')->constrained('queue_applications')->cascadeOnDelete();
                $table->foreignId('document_id')->nullable()->constrained('queue_application_documents')->nullOnDelete();
                $table->enum('decision', ['MATCH', 'MISMATCH']);
                $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('verified_by_name')->nullable();
                $table->timestamp('verified_at')->nullable();
                $table->string('statement_version', 20)->default('v1.0');
                $table->boolean('statement_agreed')->default(true);
                $table->enum('deletion_status', ['PENDING_DELETION', 'DELETED', 'DELETION_FAILED'])->default('PENDING_DELETION');
                $table->timestamp('deleted_at')->nullable();
                $table->text('deletion_error')->nullable();
                $table->unsignedTinyInteger('retry_count')->default(0);
                $table->timestamps();

                $table->index(['application_id', 'decision'], 'q_selfie_app_decision_idx');
                $table->index('deletion_status', 'q_selfie_deletion_status_idx');
            });
        }

        if (Schema::hasTable('queue_application_documents')) {
            Schema::table('queue_application_documents', function (Blueprint $table) {
                if (!Schema::hasColumn('queue_application_documents', 'is_destroyed')) {
                    $table->boolean('is_destroyed')->default(false)->after('reviewer_note');
                }
                if (!Schema::hasColumn('queue_application_documents', 'destroyed_at')) {
                    $table->timestamp('destroyed_at')->nullable()->after('is_destroyed');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_selfie_verifications');

        if (Schema::hasTable('queue_application_documents')) {
            Schema::table('queue_application_documents', function (Blueprint $table) {
                if (Schema::hasColumn('queue_application_documents', 'destroyed_at')) {
                    $table->dropColumn('destroyed_at');
                }
                if (Schema::hasColumn('queue_application_documents', 'is_destroyed')) {
                    $table->dropColumn('is_destroyed');
                }
            });
        }
    }
};
