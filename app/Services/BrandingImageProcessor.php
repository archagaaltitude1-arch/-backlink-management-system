<?php

namespace App\Services;

use App\Exceptions\InvalidBrandingImage;
use App\Support\IcoFile;
use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BrandingImageProcessor
{
    /**
     * @return array{path: string, width: int, height: int}
     */
    public function storeLogo(UploadedFile $file): array
    {
        $image = $this->read($file);
        $fitted = $this->fit($image, 250, 80);
        $path = $this->writePng($fitted, 'branding/logo-'.Str::ulid());
        $size = ['path' => $path, 'width' => imagesx($fitted), 'height' => imagesy($fitted)];
        $this->destroy($image, $fitted);

        return $size;
    }

    /**
     * @return array{path: string, source_path: string, width: int, height: int}
     */
    public function storeWallpaper(UploadedFile $file, string $position): array
    {
        $image = $this->read($file);
        $source = $this->limit($image, 3840);
        $sourcePath = $this->writeOptimized($source, 'branding/wallpaper-source-'.Str::ulid());
        $framed = $this->cover($source, 1920, 1080, $position);
        $path = $this->writeOptimized($framed, 'branding/wallpaper-'.Str::ulid());
        $result = [
            'path' => $path,
            'source_path' => $sourcePath,
            'width' => imagesx($framed),
            'height' => imagesy($framed),
        ];
        $this->destroy($image, $source, $framed);

        return $result;
    }

    /**
     * @return array{path: string, width: int, height: int}
     */
    public function recropWallpaper(string $sourcePath, string $position): array
    {
        $this->assertOwned($sourcePath);
        $binary = Storage::disk('public')->get($sourcePath);

        if (! is_string($binary) || $binary === '') {
            throw new InvalidBrandingImage('The original wallpaper could not be found. Upload it again.');
        }

        $source = $this->readBinary($binary);
        $framed = $this->cover($source, 1920, 1080, $position);
        $path = $this->writeOptimized($framed, 'branding/wallpaper-'.Str::ulid());
        $result = ['path' => $path, 'width' => imagesx($framed), 'height' => imagesy($framed)];
        $this->destroy($source, $framed);

        return $result;
    }

    /**
     * @return array{path: string, width: int, height: int}
     */
    public function storeFavicon(UploadedFile $file): array
    {
        $image = $this->read($file);
        $binary = IcoFile::encode($image);
        $path = 'branding/favicon.ico';
        Storage::disk('public')->put($path, $binary);
        $this->destroy($image);

        return ['path' => $path, 'width' => 32, 'height' => 32];
    }

    public function delete(?string $path): void
    {
        if ($path === null || $this->owned($path) === false) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    private function read(UploadedFile $file): GdImage
    {
        $binary = file_get_contents($file->getRealPath());

        if (! is_string($binary) || $binary === '') {
            throw new InvalidBrandingImage('The uploaded image could not be read.');
        }

        return $this->readBinary($binary);
    }

    private function readBinary(string $binary): GdImage
    {
        if (str_starts_with($binary, "\x00\x00\x01\x00")) {
            $image = IcoFile::decode($binary);
        } else {
            $image = @imagecreatefromstring($binary);

            if ($image === false) {
                throw new InvalidBrandingImage('Upload a valid PNG, JPG, JPEG, or WebP image.');
            }
        }

        $this->guardDimensions($image);

        return $image;
    }

    private function guardDimensions(GdImage $image): void
    {
        $width = imagesx($image);
        $height = imagesy($image);

        if ($width < 1 || $height < 1 || $width > 8000 || $height > 8000) {
            imagedestroy($image);

            throw new InvalidBrandingImage('The image must be between 1 and 8000 pixels on each side.');
        }
    }

    private function fit(GdImage $source, int $maxWidth, int $maxHeight): GdImage
    {
        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min($maxWidth / $width, $maxHeight / $height, 1);
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));

        return $this->resample($source, $targetWidth, $targetHeight, 0, 0, $width, $height);
    }

    private function limit(GdImage $source, int $maxEdge): GdImage
    {
        $width = imagesx($source);
        $height = imagesy($source);
        $edge = max($width, $height);

        if ($edge <= $maxEdge) {
            return $source;
        }

        $scale = $maxEdge / $edge;

        return $this->resample(
            $source,
            max(1, (int) round($width * $scale)),
            max(1, (int) round($height * $scale)),
            0,
            0,
            $width,
            $height,
        );
    }

    private function cover(GdImage $source, int $targetWidth, int $targetHeight, string $position): GdImage
    {
        $width = imagesx($source);
        $height = imagesy($source);
        $scale = max($targetWidth / $width, $targetHeight / $height);
        $cropWidth = min($width, max(1, (int) round($targetWidth / $scale)));
        $cropHeight = min($height, max(1, (int) round($targetHeight / $scale)));
        $cropX = max(0, (int) (($width - $cropWidth) / 2));
        $cropY = $position === 'top' ? 0 : max(0, (int) (($height - $cropHeight) / 2));

        return $this->resample($source, $targetWidth, $targetHeight, $cropX, $cropY, $cropWidth, $cropHeight);
    }

    private function resample(GdImage $source, int $targetWidth, int $targetHeight, int $cropX, int $cropY, int $cropWidth, int $cropHeight): GdImage
    {
        $target = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($target, false);
        imagesavealpha($target, true);
        $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
        imagefilledrectangle($target, 0, 0, $targetWidth, $targetHeight, $transparent);
        imagecopyresampled($target, $source, 0, 0, $cropX, $cropY, $targetWidth, $targetHeight, $cropWidth, $cropHeight);

        return $target;
    }

    private function writePng(GdImage $image, string $basename): string
    {
        $path = $basename.'.png';
        ob_start();
        imagepng($image, null, 6);
        $binary = ob_get_clean();

        if (! is_string($binary) || $binary === '') {
            throw new InvalidBrandingImage('The image could not be saved.');
        }

        Storage::disk('public')->put($path, $binary);

        return $path;
    }

    private function writeOptimized(GdImage $image, string $basename): string
    {
        if ($this->hasTransparency($image)) {
            return $this->writePng($image, $basename);
        }

        $flattened = imagecreatetruecolor(imagesx($image), imagesy($image));
        $white = imagecolorallocate($flattened, 255, 255, 255);
        imagefilledrectangle($flattened, 0, 0, imagesx($image), imagesy($image), $white);
        imagecopy($flattened, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
        imageinterlace($flattened, true);
        $path = $basename.'.jpg';
        ob_start();
        imagejpeg($flattened, null, 90);
        $binary = ob_get_clean();
        imagedestroy($flattened);

        if (! is_string($binary) || $binary === '') {
            throw new InvalidBrandingImage('The image could not be saved.');
        }

        Storage::disk('public')->put($path, $binary);

        return $path;
    }

    private function hasTransparency(GdImage $image): bool
    {
        $width = imagesx($image);
        $height = imagesy($image);

        if (! imageistruecolor($image)) {
            return imagecolortransparent($image) >= 0;
        }

        for ($y = 0; $y < $height; $y += 8) {
            for ($x = 0; $x < $width; $x += 8) {
                $alpha = (imagecolorat($image, $x, $y) >> 24) & 0x7F;

                if ($alpha > 8) {
                    return true;
                }
            }
        }

        return false;
    }

    private function assertOwned(string $path): void
    {
        if ($this->owned($path) === false) {
            throw new InvalidBrandingImage('The stored image path is not valid.');
        }
    }

    private function owned(string $path): bool
    {
        return preg_match('#^branding/[A-Za-z0-9][A-Za-z0-9._-]*$#', $path) === 1;
    }

    private function destroy(GdImage ...$images): void
    {
        $seen = [];

        foreach ($images as $image) {
            $id = spl_object_id($image);

            if (isset($seen[$id])) {
                continue;
            }

            $seen[$id] = true;
            imagedestroy($image);
        }
    }
}
