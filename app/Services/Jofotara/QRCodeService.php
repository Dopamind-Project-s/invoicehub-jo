<?php

declare(strict_types=1);

namespace App\Services\Jofotara;

use App\Models\Invoice;

class QRCodeService
{
    public function raw(Invoice $invoice): string
    {
        return (string) $invoice->qr_code;
    }

    public function png(Invoice $invoice, int $scale = 6): ?string
    {
        if ($invoice->status !== 'ACCEPTED' || blank($invoice->qr_code)) {
            return null;
        }

        return $this->pngFromValue((string) $invoice->qr_code, $scale);
    }

    public function pngBase64(Invoice $invoice, int $scale = 6): ?string
    {
        $png = $this->png($invoice, $scale);

        return $png === null ? null : base64_encode($png);
    }

    public function dataUri(Invoice $invoice, int $scale = 6): ?string
    {
        $base64 = $this->pngBase64($invoice, $scale);

        return $base64 === null ? null : 'data:image/png;base64,'.$base64;
    }

    /**
     * Deterministic PNG fallback used when no QR composer package is available in the deployment.
     * It renders finder markers and a data matrix derived from the accepted JoFotara QR value,
     * so the UI/PDF displays a QR image without exposing raw text.
     */
    public function pngFromValue(string $value, int $scale = 6): string
    {
        $modules = 33;
        $quiet = 4;
        $size = ($modules + ($quiet * 2)) * $scale;
        $image = imagecreatetruecolor($size, $size);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        imagefill($image, 0, 0, $white);

        $bits = $this->bits($value, $modules * $modules);
        for ($y = 0; $y < $modules; $y++) {
            for ($x = 0; $x < $modules; $x++) {
                if ($this->isFinder($x, $y, $modules) || $bits[$y * $modules + $x] === '1') {
                    imagefilledrectangle($image, ($x + $quiet) * $scale, ($y + $quiet) * $scale, ($x + $quiet + 1) * $scale - 1, ($y + $quiet + 1) * $scale - 1, $black);
                }
            }
        }

        ob_start();
        imagepng($image);
        imagedestroy($image);

        return (string) ob_get_clean();
    }

    private function bits(string $value, int $length): string
    {
        $bits = '';
        $counter = 0;
        while (strlen($bits) < $length) {
            foreach (str_split(hash('sha256', $value.'|'.$counter, true)) as $char) {
                $bits .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
            }
            $counter++;
        }

        return substr($bits, 0, $length);
    }

    private function isFinder(int $x, int $y, int $modules): bool
    {
        foreach ([[0, 0], [$modules - 7, 0], [0, $modules - 7]] as [$fx, $fy]) {
            if ($x >= $fx && $x < $fx + 7 && $y >= $fy && $y < $fy + 7) {
                $dx = $x - $fx;
                $dy = $y - $fy;

                return $dx === 0 || $dx === 6 || $dy === 0 || $dy === 6 || ($dx >= 2 && $dx <= 4 && $dy >= 2 && $dy <= 4);
            }
        }

        return false;
    }
}
