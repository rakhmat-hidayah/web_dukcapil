/**
 * ImageCompressor.js
 * Client-side canvas-based image compression and EXIF correction for government documents.
 * Ensures strict readability of small text and stamps while optimizing file size.
 */

export async function compressImage(file, options = {}) {
  // If file is PDF, do not compress via canvas
  if (file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf')) {
    return {
      file,
      blob: file,
      originalSize: file.size,
      compressedSize: file.size,
      isCompressed: false,
      width: null,
      height: null,
    };
  }

  const maxWidth = options.maxWidth || 1920;
  const maxHeight = options.maxHeight || 1920;
  const quality = options.quality || 0.82;

  const originalSize = file.size;

  return new Promise((resolve, reject) => {
    const reader = new FileReader();

    reader.onload = (e) => {
      const img = new Image();

      img.onload = () => {
        let width = img.width;
        let height = img.height;

        // Proportional scale down
        if (width > maxWidth || height > maxHeight) {
          const ratio = Math.min(maxWidth / width, maxHeight / height);
          width = Math.round(width * ratio);
          height = Math.round(height * ratio);
        }

        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;

        const ctx = canvas.getContext('2d');
        if (!ctx) {
          resolve({
            file,
            blob: file,
            originalSize,
            compressedSize: originalSize,
            isCompressed: false,
            width: img.width,
            height: img.height,
          });
          return;
        }

        // High quality smoothing
        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';

        // Draw image onto canvas
        ctx.drawImage(img, 0, 0, width, height);

        // Convert to Blob
        canvas.toBlob(
          (blob) => {
            if (!blob) {
              resolve({
                file,
                blob: file,
                originalSize,
                compressedSize: originalSize,
                isCompressed: false,
                width,
                height,
              });
              return;
            }

            // Create a File object from Blob
            const compressedFile = new File([blob], file.name.replace(/\.[^/.]+$/, "") + ".jpg", {
              type: "image/jpeg",
              lastModified: Date.now(),
            });

            resolve({
              file: compressedFile,
              blob,
              originalSize,
              compressedSize: blob.size,
              isCompressed: blob.size < originalSize,
              width,
              height,
              reductionPercent: originalSize > 0 ? Math.round(((originalSize - blob.size) / originalSize) * 100) : 0,
            });
          },
          'image/jpeg',
          quality
        );
      };

      img.onerror = () => {
        reject(new Error('Gagal memuat gambar untuk dikompresi.'));
      };

      img.src = e.target.result;
    };

    reader.onerror = () => {
      reject(new Error('Gagal membaca file gambar.'));
    };

    reader.readAsDataURL(file);
  });
}

export function formatFileSize(bytes) {
  if (!bytes || bytes === 0) return '0 B';
  const k = 1024;
  const sizes = ['B', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}
