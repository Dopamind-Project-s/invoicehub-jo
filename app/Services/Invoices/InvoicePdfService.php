<?php

declare(strict_types=1);

namespace App\Services\Invoices;

use App\Models\Invoice;
use Illuminate\Http\Response;

class InvoicePdfService
{
    public function __construct(private readonly InvoicePdfRenderer $renderer) {}

    public function download(Invoice $invoice): Response
    {
        return $this->renderer->download($invoice, null, ($invoice->invoice_number ?: 'invoice').'-printable.pdf');
    }

    public function html(Invoice $invoice): string
    {
        return $this->renderer->html($invoice);
    }

    public function preview(Invoice $invoice): Response
    {
        return response($this->html($invoice), 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}
