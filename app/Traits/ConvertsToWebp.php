<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait ConvertsToWebp
{
    protected function storeAsWebp(UploadedFile $file, string $directory, int $quality = 85, int $maxDimension = 1280): string
    {
        $image = match ($file->getMimeType()) {
            'image/jpeg' => imagecreatefromjpeg($file->getRealPath()),
            'image/png'  => imagecreatefrompng($file->getRealPath()),
            'image/gif'  => imagecreatefromgif($file->getRealPath()),
            'image/webp' => imagecreatefromwebp($file->getRealPath()),
            default      => null,
        };

        if (! $image) {
            return $file->store($directory, 'public');
        }

        $width  = imagesx($image);
        $height = imagesy($image);

        // Scale down so the longest side fits within $maxDimension; never upscale.
        $scale     = min(1, $maxDimension / max($width, $height));
        $newWidth  = max(1, (int) round($width * $scale));
        $newHeight = max(1, (int) round($height * $scale));

        // Render onto a white canvas: this both resizes and flattens any transparency.
        $canvas = imagecreatetruecolor($newWidth, $newHeight);
        $white  = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, $newWidth, $newHeight, $white);
        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($image);

        $filename = $directory . '/' . Str::uuid() . '.webp';

        Storage::disk('public')->makeDirectory($directory);

        imagewebp($canvas, Storage::disk('public')->path($filename), $quality);
        imagedestroy($canvas);

        return $filename;
    }
}
