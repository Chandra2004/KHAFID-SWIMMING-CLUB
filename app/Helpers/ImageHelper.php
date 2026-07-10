<?php

namespace App\Helpers;

class ImageHelper
{
    /**
     * Konversi gambar ke WebP dan simpan ke folder public/uploads
     *
     * @param mixed $file File dari request atau livewire
     * @param string $folder Nama sub-folder di dalam uploads
     * @param string|null $oldFile Path file lama yang ingin dihapus (optional)
     * @param int $quality Kualitas gambar (1-100)
     * @return string|null Path file yang disimpan
     */
    public static function uploadToWebp($file, $folder, $oldFile = null, $quality = 80)
    {
        if (!$file) return null;

        // Jika ada file lama, hapus dari server
        if ($oldFile) {
            $oldPath = public_path($oldFile);
            if (file_exists($oldPath) && is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        // Penentuan folder: Dokumen sensitif masuk ke storage/app (aman),
        // Foto profil masuk ke public/uploads (akses cepat).
        $targetDir = 'uploads/' . $folder;
        $sensitiveFolders = ['ktp_documents', 'akta_documents', 'kk_documents'];

        if (in_array($folder, $sensitiveFolders)) {
            $uploadPath = storage_path('app/' . $targetDir);
        } else {
            $uploadPath = public_path($targetDir);
        }

        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        // Nama file unik
        $filename = time() . '_' . uniqid() . '.webp';
        $fullPath = $uploadPath . '/' . $filename;

        // Ambil info mime-type dengan peredam error (@)
        $imageInfo = @getimagesize($file->getRealPath());

        if (!$imageInfo) {
            return null; // File bukan gambar yang valid
        }

        $mime = $imageInfo['mime'];

        // Buat resource gambar berdasarkan tipe asli
        try {
            switch ($mime) {
                case 'image/jpeg': $image = @imagecreatefromjpeg($file->getRealPath()); break;
                case 'image/png':  $image = @imagecreatefrompng($file->getRealPath()); break;
                case 'image/webp': $image = @imagecreatefromwebp($file->getRealPath()); break;
                case 'image/gif':  $image = @imagecreatefromgif($file->getRealPath()); break;
                default: return null;
            }
        } catch (\Exception $e) {
            return null;
        }

        if (!$image) return null;

        // --- AUTO RESIZE LOGIC ---
        $maxWidth = 1920;
        $maxHeight = 1920;
        $width = imagesx($image);
        $height = imagesy($image);

        if ($width > $maxWidth || $height > $maxHeight) {
            $ratio = $width / $height;
            if ($ratio > 1) {
                // Landscape
                $newWidth = $maxWidth;
                $newHeight = $maxWidth / $ratio;
            } else {
                // Portrait
                $newHeight = $maxHeight;
                $newWidth = $maxHeight * $ratio;
            }

            $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

            // Pertahankan transparansi untuk hasil resize
            imagealphablending($resizedImage, false);
            imagesavealpha($resizedImage, true);

            imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $resizedImage;
        }
        // --- END AUTO RESIZE LOGIC ---

        // Pertahankan transparansi untuk PNG/WebP
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        // Simpan sebagai WebP
        imagewebp($image, $fullPath, $quality);
        imagedestroy($image);

        // Kembalikan path relatif untuk database
        return $targetDir . '/' . $filename;
    }

    /**
     * Hapus file dari folder public
     *
     * @param string|null $path Path relatif file
     * @return bool
     */
    public static function deleteFile($path)
    {
        if (!$path) return false;

        $fullPath = public_path($path);
        if (file_exists($fullPath) && is_file($fullPath)) {
            return @unlink($fullPath);
        }

        return false;
    }
}
