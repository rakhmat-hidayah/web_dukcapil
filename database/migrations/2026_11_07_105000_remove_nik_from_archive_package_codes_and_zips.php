<?php

use App\Models\Archive\ArchivePackage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Sanitize all existing archive packages so citizen NIK is not exposed in package_code or physical zip filenames.
     */
    public function up(): void
    {
        $packages = ArchivePackage::all();

        foreach ($packages as $package) {
            $oldCode = $package->package_code;
            if (!preg_match('/_\d{16}/', $oldCode)) {
                continue;
            }

            $newCode = preg_replace('/_\d{16}/', '', $oldCode);

            // Ensure uniqueness
            $candidateCode = $newCode;
            $counter = 1;
            while (ArchivePackage::where('package_code', $candidateCode)->where('id', '!=', $package->id)->exists()) {
                $candidateCode = "{$newCode}_V{$counter}";
                $counter++;
            }
            $finalCode = $candidateCode;

            // Handle physical zip file renaming
            $newZipRelPath = $package->zip_path;
            if (!empty($package->zip_path)) {
                $oldZipPath = storage_path('app/private/' . $package->zip_path);
                $newZipRelPath = 'archives/' . $finalCode . '.zip';
                $newZipPath = storage_path('app/private/' . $newZipRelPath);

                if (File::exists($oldZipPath)) {
                    if ($oldZipPath !== $newZipPath) {
                        if (File::exists($newZipPath)) {
                            File::delete($newZipPath);
                        }
                        File::move($oldZipPath, $newZipPath);
                    }
                }
            }

            // Update package record
            $package->update([
                'package_code' => $finalCode,
                'zip_path' => $newZipRelPath,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Citizen privacy protection: cannot and should not restore exposed NIKs
    }
};
