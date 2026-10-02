<?php

namespace Database\Seeders;

use Dskripchenko\LaravelAdminMedia\Services\MediaService;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;

/**
 * Generates the demo images with GD — no network, no binary fixtures in the
 * repository — and puts them into the media library: product shots and
 * author avatars. The same seed always draws the same pictures.
 */
class MediaSeeder extends Seeder
{
    public const PRODUCT_IMAGES = 30;

    public const AVATARS = 8;

    /** Hues of the product shots, one per image (cycled). */
    private const HUES = [210, 12, 145, 265, 38, 190, 330, 95, 0, 230, 170, 50, 285, 120, 20];

    public function run(MediaService $media): void
    {
        mt_srand(2026);

        for ($i = 0; $i < self::PRODUCT_IMAGES; $i++) {
            $file = $this->productShot($i);
            $media->upload($file, 'products', 'product', [
                'alt' => 'Product shot '.($i + 1),
                'title' => 'product-'.($i + 1),
            ]);
            @unlink($file->getRealPath());
        }

        for ($i = 0; $i < self::AVATARS; $i++) {
            $file = $this->avatar($i);
            $media->upload($file, 'avatars', 'avatar', [
                'alt' => 'Author avatar '.($i + 1),
                'title' => 'avatar-'.($i + 1),
            ]);
            @unlink($file->getRealPath());
        }
    }

    private function productShot(int $index): UploadedFile
    {
        $size = 800;
        $image = imagecreatetruecolor($size, $size);
        imagealphablending($image, true);
        $hue = self::HUES[$index % count(self::HUES)];

        // A soft vertical gradient for the "studio" background.
        for ($y = 0; $y < $size; $y++) {
            [$r, $g, $b] = $this->hsl($hue, 0.35, 0.93 - 0.12 * $y / $size);
            imageline($image, 0, $y, $size, $y, imagecolorallocate($image, $r, $g, $b));
        }

        // A floor shadow and an abstract "object" made of a few shapes.
        imagefilledellipse($image, 400, 650, 460, 70, imagecolorallocatealpha($image, 0, 0, 0, 105));
        [$r, $g, $b] = $this->hsl($hue, 0.55, 0.48);
        $main = imagecolorallocate($image, $r, $g, $b);
        [$r, $g, $b] = $this->hsl(($hue + 25) % 360, 0.6, 0.62);
        $accent = imagecolorallocate($image, $r, $g, $b);
        $light = imagecolorallocatealpha($image, 255, 255, 255, 80);

        switch ($index % 4) {
            case 0: // a box
                imagefilledrectangle($image, 250, 290, 550, 640, $main);
                imagefilledrectangle($image, 250, 290, 550, 340, $accent);
                imagefilledrectangle($image, 270, 360, 300, 620, $light);
                break;
            case 1: // a bottle
                imagefilledrectangle($image, 360, 170, 440, 280, $accent);
                imagefilledellipse($image, 400, 470, 260, 380, $main);
                imagefilledrectangle($image, 270, 330, 530, 640, $main);
                imagefilledellipse($image, 340, 420, 40, 160, $light);
                break;
            case 2: // a sphere on a stand
                imagefilledrectangle($image, 330, 560, 470, 640, $accent);
                imagefilledellipse($image, 400, 400, 340, 340, $main);
                imagefilledellipse($image, 340, 330, 90, 70, $light);
                break;
            default: // a device
                imagefilledrectangle($image, 230, 230, 570, 600, $main);
                imagefilledrectangle($image, 255, 255, 545, 545, $accent);
                imagefilledellipse($image, 400, 573, 30, 30, $light);
                imagefilledrectangle($image, 330, 600, 470, 640, $main);
                break;
        }

        return $this->toUpload($image, 'product-'.($index + 1).'.jpg');
    }

    private function avatar(int $index): UploadedFile
    {
        $size = 256;
        $image = imagecreatetruecolor($size, $size);
        $hue = (int) (($index * 47 + 15) % 360);
        [$r, $g, $b] = $this->hsl($hue, 0.45, 0.85);
        imagefill($image, 0, 0, imagecolorallocate($image, $r, $g, $b));
        [$r, $g, $b] = $this->hsl($hue, 0.4, 0.45);
        $color = imagecolorallocate($image, $r, $g, $b);
        imagefilledellipse($image, 128, 100, 96, 104, $color);   // head
        imagefilledellipse($image, 128, 250, 200, 180, $color);  // shoulders

        return $this->toUpload($image, 'avatar-'.($index + 1).'.jpg');
    }

    private function toUpload(\GdImage $image, string $name): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'demo-img');
        imagejpeg($image, $path, 85);

        return new UploadedFile($path, $name, 'image/jpeg', null, true);
    }

    /** @return array{int, int, int} */
    private function hsl(float $h, float $s, float $l): array
    {
        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $l - $c / 2;
        [$r, $g, $b] = match (true) {
            $h < 60 => [$c, $x, 0],
            $h < 120 => [$x, $c, 0],
            $h < 180 => [0, $c, $x],
            $h < 240 => [0, $x, $c],
            $h < 300 => [$x, 0, $c],
            default => [$c, 0, $x],
        };

        return [(int) round(($r + $m) * 255), (int) round(($g + $m) * 255), (int) round(($b + $m) * 255)];
    }
}
