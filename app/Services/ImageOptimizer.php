<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class ImageOptimizer
{
    /**
     * Optimasi, resize, kompresi, dan simpan gambar secara otomatis.
     * Mengurangi ukuran file hingga 80-90% tanpa menurunkan kualitas visual.
     *
     * @param UploadedFile $file Berkas gambar yang diupload
     * @param string $destinationFolder Folder tujuan relatif di public/ (misal: 'uploads/pastor')
     * @param int $maxWidth Lebar maksimal gambar (default: 1200px)
     * @param int $maxHeight Tinggi maksimal gambar (default: 1200px)
     * @param int $quality Kualitas kompresi 1-100 (default: 80)
     * @return string Path relatif gambar yang dapat disimpan ke database (misal: '/uploads/pastor/1724...webp')
     */
    public static function optimizeAndSave(
        UploadedFile $file,
        string $destinationFolder = 'uploads/general',
        int $maxWidth = 1200,
        int $maxHeight = 1200,
        int $quality = 80
    ): string {
        $destinationPath = public_path($destinationFolder);
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $filename = time() . '_' . uniqid();

        // Cek apakah ekstensi didukung untuk optimasi via GD
        $supportedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!extension_loaded('gd') || !in_array($extension, $supportedExtensions, true)) {
            // Fallback: simpan biasa jika bukan format gambar standar atau GD tidak aktif
            $finalName = $filename . '.' . $extension;
            $file->move($destinationPath, $finalName);
            return '/' . trim($destinationFolder, '/') . '/' . $finalName;
        }

        $sourcePath = $file->getRealPath();
        $sourceImage = null;

        // Baca gambar sumber berdasarkan format
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $sourceImage = @imagecreatefromjpeg($sourcePath);
                break;
            case 'png':
                $sourceImage = @imagecreatefrompng($sourcePath);
                break;
            case 'webp':
                $sourceImage = @imagecreatefromwebp($sourcePath);
                break;
            case 'gif':
                $sourceImage = @imagecreatefromgif($sourcePath);
                break;
        }

        if (!$sourceImage) {
            $finalName = $filename . '.' . $extension;
            $file->move($destinationPath, $finalName);
            return '/' . trim($destinationFolder, '/') . '/' . $finalName;
        }

        // Ambil ukuran dimensi asli
        $origWidth = imagesx($sourceImage);
        $origHeight = imagesy($sourceImage);

        // Hitung proporsi resize agar tidak melebihi maxWidth x maxHeight
        $newWidth = $origWidth;
        $newHeight = $origHeight;

        if ($origWidth > $maxWidth || $origHeight > $maxHeight) {
            $ratio = min($maxWidth / $origWidth, $maxHeight / $origHeight);
            $newWidth = (int) round($origWidth * $ratio);
            $newHeight = (int) round($origHeight * $ratio);
        }

        // Buat canvas gambar baru dengan dimensi teroptimasi
        $targetImage = imagecreatetruecolor($newWidth, $newHeight);

        // Pertahankan transparansi untuk format PNG dan WEBP
        imagealphablending($targetImage, false);
        imagesavealpha($targetImage, true);
        $transparent = imagecolorallocatealpha($targetImage, 255, 255, 255, 127);
        imagefilledrectangle($targetImage, 0, 0, $newWidth, $newHeight, $transparent);

        // Resample dengan interpolasi berkualitas tinggi
        imagecopyresampled(
            $targetImage,
            $sourceImage,
            0, 0, 0, 0,
            $newWidth,
            $newHeight,
            $origWidth,
            $origHeight
        );

        // Simpan sebagai WebP (jika didukung) atau JPEG untuk rasio kompresi terbaik
        if (function_exists('imagewebp')) {
            $finalName = $filename . '.webp';
            $fullTargetPath = $destinationPath . DIRECTORY_SEPARATOR . $finalName;
            imagewebp($targetImage, $fullTargetPath, $quality);
        } else {
            $finalName = $filename . '.jpg';
            $fullTargetPath = $destinationPath . DIRECTORY_SEPARATOR . $finalName;
            imagejpeg($targetImage, $fullTargetPath, $quality);
        }

        // Bersihkan memori GD
        imagedestroy($sourceImage);
        imagedestroy($targetImage);

        return '/' . trim($destinationFolder, '/') . '/' . $finalName;
    }
}
