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
        // 1. Profile Sections Registry
        Schema::create('profile_sections', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g. hero, about, speech, vision_mission, duties, org_chart, officials, achievements, timeline, gallery, maklumat, contact
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 2. Profile Section Settings
        Schema::create('profile_section_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('profile_sections')->onDelete('cascade');
            $table->string('layout_type')->default('default'); // e.g. grid, list, cards, classic, glassmorphism
            $table->string('bg_color')->default('transparent');
            $table->string('animation_type')->default('fade-up');
            $table->boolean('visible_desktop')->default(true);
            $table->boolean('visible_tablet')->default(true);
            $table->boolean('visible_mobile')->default(true);
            $table->json('content_data')->nullable(); // Rich JSON configuration per section
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();
        });

        // 3. Master Official Directory
        Schema::create('officials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('nip')->nullable()->index();
            $table->string('position_title'); // e.g. Kepala Dinas Kependudukan dan Pencatatan Sipil
            $table->string('rank_golongan')->nullable(); // e.g. Pembina Utama Muda / IV c
            $table->string('department')->default('Dinas Kependudukan dan Pencatatan Sipil');
            $table->string('photo')->nullable();
            $table->text('biography')->nullable();
            $table->text('main_duties')->nullable();
            $table->string('office_address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('office_hours')->nullable();
            $table->enum('status', ['active', 'inactive', 'retired', 'transferred'])->default('active');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. Official Educations Timeline
        Schema::create('official_educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('official_id')->constrained('officials')->onDelete('cascade');
            $table->string('degree'); // e.g. S2 Magister Sains, S1 Ilmu Pemerintahan
            $table->string('institution'); // e.g. Universitas Gadjah Mada
            $table->string('major')->nullable();
            $table->year('start_year')->nullable();
            $table->year('end_year')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 5. Official Career History Timeline
        Schema::create('official_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('official_id')->constrained('officials')->onDelete('cascade');
            $table->string('position_title');
            $table->string('organization');
            $table->year('start_year')->nullable();
            $table->year('end_year')->nullable();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 6. Official Achievements & Awards
        Schema::create('official_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('official_id')->constrained('officials')->onDelete('cascade');
            $table->string('title');
            $table->string('issuer')->nullable(); // e.g. Kementerian Dalam Negeri
            $table->year('year')->nullable();
            $table->text('description')->nullable();
            $table->string('document_url')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 7. Official Social Links
        Schema::create('official_social_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('official_id')->constrained('officials')->onDelete('cascade');
            $table->string('platform'); // facebook, twitter, instagram, linkedin, youtube
            $table->string('url');
            $table->string('handle')->nullable();
            $table->timestamps();
        });

        // 8. Official Linked Documents (SK, Appoint Letters)
        Schema::create('official_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('official_id')->constrained('officials')->onDelete('cascade');
            $table->string('title');
            $table->string('file_path');
            $table->string('document_type')->default('SK Jabatan');
            $table->year('year')->nullable();
            $table->timestamps();
        });

        // 9. Organization Positions Catalog
        Schema::create('organization_positions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('title');
            $table->string('rank_level')->nullable(); // e.g. Eselon II.b, Eselon III.a, Functional
            $table->string('department')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 10. Organization Tree Nodes
        Schema::create('organization_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('position_id')->nullable()->constrained('organization_positions')->onDelete('set null');
            $table->foreignId('official_id')->nullable()->constrained('officials')->onDelete('set null');
            $table->foreignId('parent_id')->nullable()->constrained('organization_nodes')->onDelete('cascade');
            $table->string('node_title');
            $table->string('color_code')->default('#2563eb');
            $table->string('icon')->default('UserCheck');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->json('layout_coords')->nullable(); // { x: 100, y: 200 } for visual editor positioning
            $table->timestamps();
        });

        // 11. Organization Layout Presets
        Schema::create('organization_layouts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('layout_type')->default('classic'); // classic, horizontal, vertical, compact, mindmap
            $table->boolean('is_default')->default(false);
            $table->json('config')->nullable();
            $table->timestamps();
        });

        // 12. Organization Settings
        Schema::create('organization_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_settings');
        Schema::dropIfExists('organization_layouts');
        Schema::dropIfExists('organization_nodes');
        Schema::dropIfExists('organization_positions');
        Schema::dropIfExists('official_documents');
        Schema::dropIfExists('official_social_links');
        Schema::dropIfExists('official_achievements');
        Schema::dropIfExists('official_histories');
        Schema::dropIfExists('official_educations');
        Schema::dropIfExists('officials');
        Schema::dropIfExists('profile_section_settings');
        Schema::dropIfExists('profile_sections');
    }
};
