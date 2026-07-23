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
        // 1. Survey Periods
        Schema::create('survey_periods', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('semester', ['1', '2'])->default('1');
            $table->year('year');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['draft', 'published', 'closed'])->default('published');
            $table->boolean('is_active')->default(true);
            $table->integer('target_respondents')->default(100);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 2. Survey Questions
        Schema::create('survey_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_period_id')->constrained('survey_periods')->cascadeOnDelete();
            $table->text('question_text');
            $table->enum('question_type', ['rating', 'multiple_choice', 'text', 'yes_no'])->default('rating');
            $table->string('service_category')->default('Umum');
            $table->integer('sort_order')->default(1);
            $table->boolean('is_required')->default(true);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        // 3. Survey Question Options (for multiple choice)
        Schema::create('survey_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_question_id')->constrained('survey_questions')->cascadeOnDelete();
            $table->string('option_label');
            $table->string('option_value');
            $table->integer('sort_order')->default(1);
            $table->timestamps();
        });

        // 4. Survey Responses
        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_period_id')->constrained('survey_periods')->cascadeOnDelete();
            $table->string('respondent_name')->nullable();
            $table->string('respondent_age')->nullable();
            $table->string('respondent_gender')->nullable();
            $table->string('respondent_education')->nullable();
            $table->string('respondent_job')->nullable();
            $table->string('service_accessed')->nullable();
            $table->decimal('ikm_score', 5, 2)->default(0);
            $table->text('suggestion')->nullable();
            $table->timestamps();
        });

        // 5. Survey Response Answers
        Schema::create('survey_response_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_response_id')->constrained('survey_responses')->cascadeOnDelete();
            $table->foreignId('survey_question_id')->constrained('survey_questions')->cascadeOnDelete();
            $table->text('answer_value');
            $table->decimal('score', 4, 2)->default(0);
            $table->timestamps();
        });

        // 6. Survey Result Statistics
        Schema::create('survey_result_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_period_id')->unique()->constrained('survey_periods')->cascadeOnDelete();
            $table->integer('total_respondents')->default(0);
            $table->decimal('average_score', 4, 2)->default(0);
            $table->decimal('ikm_value', 5, 2)->default(0);
            $table->string('service_quality_category')->default('B'); // A, B, C, D
            $table->json('index_breakdown_json')->nullable();
            $table->timestamps();
        });

        // 7. Survey Recommendations
        Schema::create('survey_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_period_id')->constrained('survey_periods')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('priority', ['high', 'medium', 'low'])->default('medium');
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('in_progress');
            $table->date('target_completion')->nullable();
            $table->string('pic')->nullable();
            $table->timestamps();
        });

        // 8. Survey Follow-up Actions
        Schema::create('survey_follow_up_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_period_id')->constrained('survey_periods')->cascadeOnDelete();
            $table->foreignId('recommendation_id')->nullable()->constrained('survey_recommendations')->nullOnDelete();
            $table->string('action_name');
            $table->text('description')->nullable();
            $table->string('responsible_unit')->default('Bidang PIAK');
            $table->integer('progress')->default(0); // 0 - 100
            $table->string('evidence_document')->nullable();
            $table->date('completion_date')->nullable();
            $table->enum('status', ['planned', 'on_track', 'completed', 'delayed'])->default('on_track');
            $table->timestamps();
        });

        // 9. Survey Publications
        Schema::create('survey_publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_period_id')->constrained('survey_periods')->cascadeOnDelete();
            $table->string('title');
            $table->text('summary')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('pdf_report_path')->nullable();
            $table->string('excel_report_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_publications');
        Schema::dropIfExists('survey_follow_up_actions');
        Schema::dropIfExists('survey_recommendations');
        Schema::dropIfExists('survey_result_statistics');
        Schema::dropIfExists('survey_response_answers');
        Schema::dropIfExists('survey_responses');
        Schema::dropIfExists('survey_question_options');
        Schema::dropIfExists('survey_questions');
        Schema::dropIfExists('survey_periods');
    }
};
