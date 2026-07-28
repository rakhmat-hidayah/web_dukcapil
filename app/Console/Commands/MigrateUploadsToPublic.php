<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class MigrateUploadsToPublic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uploads:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate files from storage/app/public to public/uploads and update all database path references';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info("====================================================");
        $this->info("  PUBLIC UPLOADS ARCHITECTURE MIGRATION ENGINE     ");
        $this->info("====================================================");

        $sourceDir = storage_path('app/public');
        $targetDir = public_path('uploads');

        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        // 1. Copy all files from storage/app/public into public/uploads
        if (File::exists($sourceDir)) {
            $this->info("1. Copying files from storage/app/public -> public/uploads...");
            $files = File::allFiles($sourceDir);
            $copiedCount = 0;

            foreach ($files as $file) {
                $relativePath = $file->getRelativePathname();
                $destFile = $targetDir . '/' . $relativePath;

                $destDir = dirname($destFile);
                if (!File::exists($destDir)) {
                    File::makeDirectory($destDir, 0755, true);
                }

                File::copy($file->getPathname(), $destFile);
                $copiedCount++;
            }

            $this->info("   ✔ Copy completed ($copiedCount files copied).");
        } else {
            $this->warn("   ⚠️ Source directory storage/app/public does not exist.");
        }

        // 2. Migrate Database Tables Paths
        $this->info("2. Updating database file path references...");

        $tablesMap = [
            'banners'            => ['image_url', 'image_path', 'image'],
            'officials'          => ['photo'],
            'news'               => ['featured_image', 'image'],
            'downloads'          => ['file_path'],
            'gallery_items'      => ['file_path', 'thumbnail_path'],
            'innovations'        => ['cover_image', 'file_attachment'],
            'ppid_documents'     => ['file_path'],
            'website_settings'   => ['value'],
            'theme_settings'     => ['value'],
            'organization_nodes' => ['photo'],
        ];

        $totalUpdatedRows = 0;

        foreach ($tablesMap as $table => $columns) {
            if (!DB::getSchemaBuilder()->hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (!DB::getSchemaBuilder()->hasColumn($table, $column)) {
                    continue;
                }

                $affected = DB::table($table)
                    ->where(function ($q) use ($column) {
                        $q->where($column, 'like', 'storage/%')
                          ->orWhere($column, 'like', '/storage/%');
                    })
                    ->update([
                        $column => DB::raw("REPLACE(REPLACE($column, '/storage/', 'uploads/'), 'storage/', 'uploads/')")
                    ]);

                $totalUpdatedRows += $affected;
            }
        }

        $this->info("   ✔ Database paths updated ($totalUpdatedRows records modified).");

        $this->newLine();
        $this->info("====================================================");
        $this->info("✔ SUCCESS: All public assets migrated to public/uploads!");
        $this->info("====================================================");

        return Command::SUCCESS;
    }
}
