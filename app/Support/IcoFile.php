<?php

namespace App\Support;

use App\Exceptions\InvalidBrandingImage;
use GdImage;

class IcoFile
{
    public static function encode(GdImage $image): string
    {
        $canvas = self::frame($image, 32, 32);
        $xor = '';
        $mask = '';

        for ($y = 31; $y >= 0; $y--) {
            $maskByte = 0;
            $maskBit = 7;

            for ($x = 0; $x < 32; $x++) {
                $color = imagecolorat($canvas, $x, $y);
                $red = ($color >> 16) & 0xFF;
                $green = ($color >> 8) & 0xFF;
                $blue = $color & 0xFF;
                $gdAlpha = ($color >> 24) & 0x7F;
                $alpha = 255 - (int) round($gdAlpha * 255 / 127);
                $xor .= pack('C4', $blue, $green, $red, $alpha);

                if ($alpha < 128) {
                    $maskByte |= (1 << $maskBit);
                }

                $maskBit--;

                if ($maskBit < 0) {
                    $mask .= chr($maskByte);
                    $maskByte = 0;
                    $maskBit = 7;
                }
            }
        }

        if ($canvas !== $image) {
            imagedestroy($canvas);
        }

        $dib = pack('VVVvvVVVVVV', 40, 32, 64, 1, 32, 0, strlen($xor), 0, 0, 0, 0);
        $imageData = $dib.$xor.$mask;
        $directory = pack('vvv', 0, 1, 1);
        $entry = pack('CCCCvvVV', 32, 32, 0, 0, 1, 32, strlen($imageData), 22);

        return $directory.$entry.$imageData;
    }

    public static function decode(string $binary): GdImage
    {
        if (strlen($binary) < 22 || ! str_starts_with($binary, "\x00\x00\x01\x00")) {
            throw new InvalidBrandingImage('The favicon file is not a valid ICO image.');
        }

        $header = unpack('vreserved/vtype/vcount', substr($binary, 0, 6));
        $count = (int) ($header['count'] ?? 0);

        if (($header['type'] ?? 0) !== 1 || $count < 1) {
            throw new InvalidBrandingImage('The favicon file is not a valid ICO image.');
        }

        $best = null;
        $bestArea = -1;

        for ($index = 0; $index < $count; $index++) {
            $offset = 6 + ($index * 16);
            $entry = unpack('Cwidth/Cheight/Ccolors/Creserved/vplanes/vbits/Vsize/Voffset', substr($binary, $offset, 16));

            if (! is_array($entry)) {
                continue;
            }

            $width = $entry['width'] === 0 ? 256 : (int) $entry['width'];
            $height = $entry['height'] === 0 ? 256 : (int) $entry['height'];
            $area = $width * $height;

            if ($area > $bestArea) {
                $bestArea = $area;
                $best = $entry;
            }
        }

        if ($best === null) {
            throw new InvalidBrandingImage('The favicon image could not be read.');
        }

        $data = substr($binary, (int) $best['offset'], (int) $best['size']);

        if (str_starts_with($data, "\x89PNG\r\n\x1a\n")) {
            $image = @imagecreatefromstring($data);

            if ($image === false) {
                throw new InvalidBrandingImage('The favicon image could not be read.');
            }

            return $image;
        }

        return self::decodeBitmap($data);
    }

    private static function frame(GdImage $image, int $width, int $height): GdImage
    {
        $sourceWidth = imagesx($image);
        $sourceHeight = imagesy($image);
        $scale = max($width / $sourceWidth, $height / $sourceHeight);
        $cropWidth = max(1, (int) round($width / $scale));
        $cropHeight = max(1, (int) round($height / $scale));
        $cropWidth = min($cropWidth, $sourceWidth);
        $cropHeight = min($cropHeight, $sourceHeight);
        $cropX = max(0, (int) (($sourceWidth - $cropWidth) / 2));
        $cropY = max(0, (int) (($sourceHeight - $cropHeight) / 2));
        $canvas = imagecreatetruecolor($width, $height);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $width, $height, $transparent);
        imagecopyresampled($canvas, $image, 0, 0, $cropX, $cropY, $width, $height, $cropWidth, $cropHeight);

        return $canvas;
    }

    private static function decodeBitmap(string $data): GdImage
    {
        if (strlen($data) < 40) {
            throw new InvalidBrandingImage('The favicon image could not be read.');
        }

        $info = unpack('Vsize/Vwidth/Vheight/vplanes/vbits', substr($data, 0, 16));
        $width = (int) ($info['width'] ?? 0);
        $storedHeight = (int) ($info['height'] ?? 0);
        $height = intdiv($storedHeight, 2) ?: $storedHeight;
        $bits = (int) ($info['bits'] ?? 0);
        $headerSize = (int) ($info['size'] ?? 40);

        if ($width < 1 || $height < 1 || $width > 256 || $height > 256 || ! in_array($bits, [24, 32], true)) {
            throw new InvalidBrandingImage('Upload a PNG, JPG, or 32-bit ICO favicon.');
        }

        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        $bytesPerPixel = intdiv($bits, 8);
        $rowStride = (int) (ceil(($bits * $width) / 32) * 4);

        for ($y = 0; $y < $height; $y++) {
            $fileRow = $height - 1 - $y;
            $row = substr($data, $headerSize + ($fileRow * $rowStride), $rowStride);

            for ($x = 0; $x < $width; $x++) {
                $offset = $x * $bytesPerPixel;
                $blue = ord($row[$offset] ?? "\0");
                $green = ord($row[$offset + 1] ?? "\0");
                $red = ord($row[$offset + 2] ?? "\0");
                $alpha = $bits === 32 ? ord($row[$offset + 3] ?? "\xFF") : 255;
                $gdAlpha = 127 - (int) round($alpha * 127 / 255);
                $color = imagecolorallocatealpha($image, $red, $green, $blue, $gdAlpha);
                imagesetpixel($image, $x, $y, $color);
            }
        }

        return $image;
    }
}
