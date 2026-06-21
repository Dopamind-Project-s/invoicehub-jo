<?php

declare(strict_types=1);

namespace App\Services\Invoices;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class InvoicePdfService
{
    public function __construct(private readonly InvoiceBrandingService $branding) {}

    public function download(Invoice $invoice): Response
    {
        $invoice->load(['company.settings', 'contact', 'items.product']);
        $branding = $this->branding->settings($invoice->company);
        $filename = $invoice->invoice_number.'-printable.pdf';

        if (class_exists(Pdf::class)) {
            return Pdf::loadView('company.invoices.printable', compact('invoice', 'branding'))->setPaper('a4', 'portrait')->download($filename);
        }

        return response()->view('company.invoices.printable', compact('invoice', 'branding'), 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'inline; filename="'.$filename.'.html"',
        ]);
    }

    public function html(Invoice $invoice): string
    {
        $invoice->load(['company.settings', 'contact', 'items.product']);
        $branding = $this->branding->settings($invoice->company);

        return view('company.invoices.printable', compact('invoice', 'branding'))->render();
    }
}
