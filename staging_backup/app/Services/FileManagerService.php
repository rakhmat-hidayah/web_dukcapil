<?php

namespace App\Services;

use App\Models\FileFolder;
use App\Models\FileEntry;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class FileManagerService
{
    /**
     * Create a new folder.
     */
    public static function createFolder(string $name, ?int $parentId = null, ?int $userId = null): FileFolder
    {
        return FileFolder::create([
            'name' => trim($name),
            'parent_id' => $parentId,
            'created_by' => $userId,
        ]);
    }

    /**
     * Upload and register a new file entry.
     */
    public static function uploadFile(UploadedFile $file, ?int $folderId = null, ?int $userId = null, ?int $parentVersionId = null): FileEntry
    {
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getClientMimeType();
        $size = $file->getSize();

        // Generate a unique path name
        $uuid = Str::uuid()->toString();
        $filename = "{$uuid}.{$extension}";
        $subDirectory = 'file_manager/' . date('Y/m');
        $storagePath = "{$subDirectory}/{$filename}";

        // Check if image for optimization
        $isImage = str_contains($mimeType, 'image') && in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif']);

        if ($isImage) {
            try {
                // Read, resize if necessary, and compress image
                $img = Image::decode($file);
                
                // Downscale if image is overly large (e.g. > 1600px width/height)
                $width = $img->width();
                $height = $img->height();
                $maxDim = 1600;
                
                if ($width > $maxDim || $height > $maxDim) {
                    $img->scaleDown(width: $maxDim, height: $maxDim);
                }

                // Compress (quality 80) and save using v4 encode API
                $encoder = match(strtolower($extension)) {
                    'webp' => new \Intervention\Image\Encoders\WebpEncoder(quality: 80),
                    'jpg', 'jpeg' => new \Intervention\Image\Encoders\JpegEncoder(quality: 80),
                    'png' => new \Intervention\Image\Encoders\PngEncoder(),
                    'gif' => new \Intervention\Image\Encoders\GifEncoder(),
                    default => new \Intervention\Image\Encoders\JpegEncoder(quality: 80),
                };
                $compressedData = (string) $img->encode($encoder);
                
                Storage::disk('public')->put($storagePath, $compressedData);
                $size = strlen($compressedData); // update size to compressed size
            } catch (\Exception $e) {
                // If optimization fails, fallback to normal storage
                \Log::warning('Image optimization failed, falling back: ' . $e->getMessage());
                $file->storeAs($subDirectory, $filename, 'public');
            }
        } else {
            // Store file as is
            $file->storeAs($subDirectory, $filename, 'public');
        }

        // Determine version number
        $version = 1;
        if ($parentVersionId) {
            $parentFile = FileEntry::findOrFail($parentVersionId);
            $version = $parentFile->version + 1;
            
            // Set the parent of all subversions to be the root ancestor
            $rootParentId = $parentFile->parent_version_id ?? $parentFile->id;
            $parentVersionId = $rootParentId;
        }

        return FileEntry::create([
            'folder_id' => $folderId,
            'name' => $originalName,
            'original_name' => $originalName,
            'path' => $storagePath,
            'mime_type' => $mimeType,
            'size' => $size,
            'extension' => $extension,
            'disk' => 'public',
            'version' => $version,
            'parent_version_id' => $parentVersionId,
            'created_by' => $userId,
        ]);
    }

    /**
     * Upload a new version for an existing file entry.
     */
    public static function uploadNewVersion(int $fileId, UploadedFile $file, ?int $userId = null): FileEntry
    {
        $originalEntry = FileEntry::findOrFail($fileId);
        
        // Root parent should be the top-level parent if this is already a subversion
        $rootParentId = $originalEntry->parent_version_id ?? $originalEntry->id;

        // Perform standard upload but link as new version
        $newEntry = self::uploadFile($file, $originalEntry->folder_id, $userId, $rootParentId);
        
        return $newEntry;
    }

    /**
     * Delete folder (cascades database-level delete) and physically deletes all underlying files.
     */
    public static function deleteFolder(int $folderId): void
    {
        $folder = FileFolder::findOrFail($folderId);
        
        // Delete all files physically in this folder and subfolders
        foreach ($folder->files as $file) {
            self::deleteFilePhysically($file);
        }
        
        foreach ($folder->children as $child) {
            self::deleteFolder($child->id);
        }
        
        $folder->delete();
    }

    /**
     * Soft delete file entry.
     */
    public static function softDeleteFile(int $fileId): bool
    {
        $file = FileEntry::findOrFail($fileId);
        
        // Soft delete this and all its historical versions
        FileEntry::where('parent_version_id', $file->id)->delete();
        
        return $file->delete();
    }

    /**
     * Physically delete file from disk.
     */
    private static function deleteFilePhysically(FileEntry $file): void
    {
        if (Storage::disk($file->disk)->exists($file->path)) {
            Storage::disk($file->disk)->delete($file->path);
        }
        $file->forceDelete();
    }

    /**
     * Retrieve directory contents.
     * Displays folders and only the LATEST active versions of files!
     */
    public static function getContents(?int $folderId = null, string $search = ''): array
    {
        $foldersQuery = FileFolder::query();
        $filesQuery = FileEntry::query()->whereNull('parent_version_id'); // only fetch primary versions

        if ($search) {
            $foldersQuery->where('name', 'like', "%{$search}%");
            $filesQuery->where('name', 'like', "%{$search}%");
        } else {
            $foldersQuery->where('parent_id', $folderId);
            $filesQuery->where('folder_id', $folderId);
        }

        return [
            'folders' => $foldersQuery->with('creator:id,name')->orderBy('name')->get(),
            'files' => $filesQuery->with(['creator:id,name', 'versions'])->orderBy('name')->get(),
        ];
    }
}
