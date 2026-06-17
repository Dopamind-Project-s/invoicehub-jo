<?php

declare(strict_types=1);

namespace App\Services\Jofotara;

use App\Models\Invoice;

class QRCodeService
{
    public function raw(Invoice $invoice): string
    {
        return json_encode(['seller' => $invoice->supplier?->legal_name_ar, 'tax_number' => $invoice->supplier?->tax_number, 'invoice_number' => $invoice->invoice_number, 'icv' => $invoice->icv, 'date' => $invoice->issue_date?->format('Y-m-d'), 'total' => $invoice->payable_amount, 'tax' => $invoice->tax_amount, 'hash' => $invoice->xml_hash], JSON_UNESCAPED_UNICODE) ?: '';
    }

    public function base64(Invoice $invoice): string
    {
        return base64_encode($this->raw($invoice));
    }

    public function pngDataUri(Invoice $invoice): string
    {
        return 'data:image/png;base64,'.$this->base64($invoice);
    }
}
