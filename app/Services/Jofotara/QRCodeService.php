<?php

declare(strict_types=1);

namespace App\Services\Jofotara;

use App\Models\Invoice;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRCodeService
{
    public function hasOfficialQr(Invoice $invoice): bool
    {
        return filled($this->officialValue($invoice));
    }

    public function officialValue(Invoice $invoice): ?string
    {
        $status = strtoupper((string) $invoice->jofotara_status);
        $validation = strtoupper((string) $invoice->jofotara_validation_result);
        if (! in_array($status, ['ACCEPTED', 'SUBMITTED'], true) || ($validation !== '' && $validation !== 'PASS')) {
            return null;
        }
        if (blank($invoice->jofotara_uuid) || blank($invoice->jofotara_qr)) {
            return null;
        }

        return trim((string) $invoice->jofotara_qr);
    }

    public function raw(Invoice $invoice): string
    {
        return (string) ($this->officialValue($invoice) ?: '');
    }

    /** @return array{mime:string,bytes:string}|null */
    public function image(Invoice $invoice, int $size = 180): ?array
    {
        $value = $this->officialValue($invoice);
        if (blank($value)) {
            return null;
        }
        $decoded = $this->decodeImage($value);
        if ($decoded !== null && in_array($decoded['mime'], ['image/png', 'image/svg+xml'], true)) {
            return $decoded;
        }
        $payload = $decoded !== null && $decoded['mime'] === 'text/plain' ? $decoded['bytes'] : $value;
        $svg = (string) QrCode::format('svg')->encoding('UTF-8')->size($size)->margin(1)->generate($payload);

        return ['mime' => 'image/svg+xml', 'bytes' => $svg];
    }

    public function png(Invoice $invoice, int $scale = 6): ?string
    {
        $image = $this->image($invoice, max(120, $scale * 36));

        return $image && $image['mime'] === 'image/png' ? $image['bytes'] : null;
    }

    public function pngBase64(Invoice $invoice, int $scale = 6): ?string
    {
        $png = $this->png($invoice, $scale);

        return $png === null ? null : base64_encode($png);
    }

    public function dataUri(Invoice $invoice, int $scale = 6): ?string
    {
        $image = $this->image($invoice, max(120, $scale * 36));

        return $image === null ? null : 'data:'.$image['mime'].';base64,'.base64_encode($image['bytes']);
    }

    /** @return array{mime:string,bytes:string}|null */
    private function decodeImage(string $value): ?array
    {
        $value = trim($value);
        if (preg_match('#^data:(image/(?:png|svg\+xml));base64,(.+)$#i', $value, $matches)) {
            $bytes = base64_decode($matches[2], true);

            return $bytes === false ? null : ['mime' => strtolower($matches[1]), 'bytes' => $bytes];
        }
        if (str_starts_with($value, '<svg')) {
            return ['mime' => 'image/svg+xml', 'bytes' => $value];
        }
        $bytes = base64_decode($value, true);
        if ($bytes !== false) {
            if (str_starts_with($bytes, "\x89PNG\r\n\x1a\n")) {
                return ['mime' => 'image/png', 'bytes' => $bytes];
            }
            if (str_starts_with(ltrim($bytes), '<svg')) {
                return ['mime' => 'image/svg+xml', 'bytes' => $bytes];
            }
        }

        return null;
    }
}
