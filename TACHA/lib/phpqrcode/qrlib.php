<?php
// Minimal local QR-like generator for MVP (deterministic pattern from text).
class QRcode
{
    public static function png(string $text, ?string $outfile = null, string $level = 'L', int $size = 6, int $margin = 2): void
    {
        $modules = 33;
        $cell = max(2, $size);
        $imgSize = ($modules + ($margin * 2)) * $cell;

        $img = imagecreatetruecolor($imgSize, $imgSize);
        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 5, 26, 58);
        imagefill($img, 0, 0, $white);

        $seed = crc32($text);
        $drawFinder = function (int $x, int $y) use ($img, $cell, $margin, $black, $white): void {
            $x0 = ($x + $margin) * $cell;
            $y0 = ($y + $margin) * $cell;
            imagefilledrectangle($img, $x0, $y0, $x0 + (7 * $cell), $y0 + (7 * $cell), $black);
            imagefilledrectangle($img, $x0 + $cell, $y0 + $cell, $x0 + (6 * $cell), $y0 + (6 * $cell), $white);
            imagefilledrectangle($img, $x0 + (2 * $cell), $y0 + (2 * $cell), $x0 + (5 * $cell), $y0 + (5 * $cell), $black);
        };

        $drawFinder(1, 1);
        $drawFinder($modules - 9, 1);
        $drawFinder(1, $modules - 9);

        for ($y = 0; $y < $modules; $y++) {
            for ($x = 0; $x < $modules; $x++) {
                $inFinder =
                    ($x >= 1 && $x <= 8 && $y >= 1 && $y <= 8) ||
                    ($x >= $modules - 9 && $x <= $modules - 2 && $y >= 1 && $y <= 8) ||
                    ($x >= 1 && $x <= 8 && $y >= $modules - 9 && $y <= $modules - 2);

                if ($inFinder) {
                    continue;
                }

                $seed = (1103515245 * $seed + 12345) & 0x7fffffff;
                if (($seed % 100) > 48) {
                    $x0 = ($x + $margin) * $cell;
                    $y0 = ($y + $margin) * $cell;
                    imagefilledrectangle($img, $x0, $y0, $x0 + $cell, $y0 + $cell, $black);
                }
            }
        }

        if ($outfile) {
            imagepng($img, $outfile);
        } else {
            header('Content-Type: image/png');
            imagepng($img);
        }

        imagedestroy($img);
    }
}
?>

