<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            // Hero Image Caption - keterangan/caption foto hero image
            $table->text('thumbnail_caption')->nullable()->after('thumbnail');
            // Hero Image Alt Text - alt text khusus untuk SEO & aksesibilitas
            $table->string('thumbnail_alt', 255)->nullable()->after('thumbnail_caption');
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn(['thumbnail_caption', 'thumbnail_alt']);
        });
    }
};
