<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('pages')) {
            return;
        }

        // 1. Create a safe backup table if not exists
        if (!Schema::hasTable('pages_backup_20261111')) {
            DB::statement('CREATE TABLE pages_backup_20261111 AS SELECT * FROM pages');
        }

        // 2. Remove WordPress system styles & elementor dump junk
        DB::table('pages')
            ->where(function ($q) {
                $q->where('slug', 'like', 'wp-global-styles%')
                  ->orWhere('slug', 'like', 'default-kit%')
                  ->orWhere('slug', 'like', 'navigation%')
                  ->orWhereIn('title', ['Custom Styles', 'Default Kit', 'Navigation']);
            })
            ->delete();

        // 3. Deduplicate remaining pages: keep the best record for each title
        $allPages = DB::table('pages')->get();
        $grouped = $allPages->groupBy('title');

        $deleteIds = [];
        foreach ($grouped as $title => $group) {
            if ($group->count() > 1) {
                // Keep the record with the longest content, or latest ID
                $best = $group->sortByDesc(fn($p) => [strlen($p->content ?? ''), $p->id])->first();
                foreach ($group as $p) {
                    if ($p->id !== $best->id) {
                        $deleteIds[] = $p->id;
                    }
                }
            }
        }

        if (!empty($deleteIds)) {
            DB::table('pages')->whereIn('id', $deleteIds)->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('pages_backup_20261111') && Schema::hasTable('pages')) {
            DB::table('pages')->truncate();
            DB::statement('INSERT INTO pages SELECT * FROM pages_backup_20261111');
        }
    }
};
