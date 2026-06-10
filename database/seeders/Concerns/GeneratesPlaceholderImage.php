<?php

namespace Database\Seeders\Concerns;

use Illuminate\Support\Facades\File;

trait GeneratesPlaceholderImage
{
    protected function placeholderImagePath(string $name, int $width = 800, int $height = 450, string $hexColor = 'FF8C00'): string
    {
        $directory = storage_path('app/seeders/placeholders');

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $path = $directory.'/'.$name.'.jpg';

        if (File::exists($path)) {
            return $path;
        }

        if (! extension_loaded('gd')) {
            return $path;
        }

        $image = imagecreatetruecolor($width, $height);
        $r = hexdec(substr($hexColor, 0, 2));
        $g = hexdec(substr($hexColor, 2, 2));
        $b = hexdec(substr($hexColor, 4, 2));
        $background = imagecolorallocate($image, $r, $g, $b);
        imagefill($image, 0, 0, $background);

        $textColor = imagecolorallocate($image, 255, 255, 255);
        $label = strtoupper(str_replace(['-', '_'], ' ', pathinfo($name, PATHINFO_FILENAME)));
        imagestring($image, 5, (int) ($width / 2 - strlen($label) * 4), (int) ($height / 2 - 8), $label, $textColor);

        imagejpeg($image, $path, 90);
        imagedestroy($image);

        return $path;
    }

    protected function attachPlaceholderMedia(
        object $model,
        string $collection,
        string $filename,
        int $width = 800,
        int $height = 450,
        string $color = 'FF8C00'
    ): void {
        if (! method_exists($model, 'addMedia')) {
            return;
        }

        $path = $this->placeholderImagePath($filename, $width, $height, $color);

        if (! File::exists($path)) {
            return;
        }

        $model->addMedia($path)->preservingOriginal()->toMediaCollection($collection);
    }
}
