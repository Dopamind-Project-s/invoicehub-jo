<?php

namespace App\Services\Invoices;

use App\Models\Invoice;
use App\Models\InvoiceShare;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class InvoiceShareService
{
    public function create(Invoice $invoice, string $channel = 'link', ?string $recipient = null, ?\DateTimeInterface $expiresAt = null): InvoiceShare
    {
        return InvoiceShare::query()->create([
            'invoice_id' => $invoice->id,
            'company_id' => $invoice->company_id,
            'token' => Str::random(48),
            'channel' => $channel,
            'recipient' => $recipient,
            'expires_at' => $expiresAt,
            'created_by' => Auth::id(),
        ]);
    }

    public function payload(InvoiceShare $share): array
    {
        $url = route('invoices.shared.show', $share->token);
        $invoice = $share->invoice;
        $text = 'فاتورة '.$invoice->invoice_number.' - '.$invoice->grand_total.' '.$invoice->currency;

        return [
            'url' => $url,
            'copy_link' => $url,
            'whatsapp_url' => 'https://wa.me/?text='.rawurlencode($text.' '.$url),
            'mailto_url' => 'mailto:?subject='.rawurlencode('Invoice '.$invoice->invoice_number).'&body='.rawurlencode($text."\n".$url),
            'text' => $text,
        ];
    }
}
