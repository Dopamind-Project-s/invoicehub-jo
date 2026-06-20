<?php

declare(strict_types=1);

namespace App\Services\Invoices;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class InvoicePdfService
{
    public function download(Invoice $invoice): Response
    {
        $invoice->load(['company', 'contact', 'items.product']);
        $filename = $invoice->invoice_number.'-printable.pdf';

        if (class_exists(Pdf::class)) {
            return Pdf::loadView('company.invoices.printable', compact('invoice'))->setPaper('a4', 'portrait')->download($filename);
        }

        return response()->view('company.invoices.printable', compact('invoice'), 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'inline; filename="'.$filename.'.html"',
        ]);
    }
}
