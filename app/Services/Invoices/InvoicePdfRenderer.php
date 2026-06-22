<?php

declare(strict_types=1);

namespace App\Services\Invoices;

use App\Models\Invoice;
use App\Models\InvoiceTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Spatie\Browsershot\Browsershot;

class InvoicePdfRenderer
{
    public function __construct(private readonly InvoiceTemplateDataFactory $factory) {}

    public function html(Invoice $invoice, ?InvoiceTemplate $template = null): string
    {
        $data = $this->factory->make($invoice, $template);
        return view($data->template->view_path ?: 'company.invoice-templates.render.arabic-classic', ['data' => $data])->render();
    }

    public function download(Invoice $invoice, ?InvoiceTemplate $template = null, ?string $filename = null): Response
    {
        $filename ??= ($invoice->invoice_number ?: 'invoice').'.pdf';
        $html = $this->html($invoice, $template);
        $pdf = $this->pdfBytes($html);
        return response($pdf, 200, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'attachment; filename="'.$filename.'"']);
    }

    public function stream(Invoice $invoice, ?InvoiceTemplate $template = null, ?string $filename = null): Response
    {
        $filename ??= 'invoice-preview.pdf';
        $html = $this->html($invoice, $template);
        if (request()->boolean('download')) return $this->download($invoice, $template, $filename);
        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    private function pdfBytes(string $html): string
    {
        try {
            if (class_exists(Browsershot::class) && version_compare(PHP_VERSION, '8.1.0', '>=')) {
                return Browsershot::html($html)->format('A4')->margins(8, 8, 8, 8)->showBackground()->pdf();
            }
        } catch (\Throwable) {}

        return Pdf::loadHTML($html)->setPaper('a4', 'portrait')->output();
    }
}
