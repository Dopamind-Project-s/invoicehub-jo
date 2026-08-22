<?php

declare(strict_types=1);

namespace App\Services\Invoices;

use App\Models\Invoice;
use App\Models\InvoiceTemplate;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use RuntimeException;
use Throwable;

class InvoicePdfRenderer
{
    public function __construct(
        private readonly InvoiceTemplateDataFactory $factory,
        private readonly InvoiceTemplateResolver $resolver,
    ) {}

    public function html(Invoice $invoice, ?InvoiceTemplate $template = null): string
    {
        return $this->renderHtml($invoice, $template);
    }

    public function download(Invoice $invoice, ?InvoiceTemplate $template = null, ?string $filename = null): Response
    {
        $filename ??= ($invoice->invoice_number ?: 'invoice').'.pdf';
        try {
            $pdf = $this->pdfBytes($invoice, $template);
        } catch (Throwable $exception) {
            report($exception);

            return response('تعذر إنشاء ملف PDF حالياً. يرجى المحاولة مرة أخرى.', 503, ['Content-Type' => 'text/plain; charset=UTF-8']);
        }

        return response($pdf, 200, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'attachment; filename="'.$filename.'"']);
    }

    public function stream(Invoice $invoice, ?InvoiceTemplate $template = null, ?string $filename = null): Response
    {
        $filename ??= 'invoice-preview.pdf';
        $html = $this->html($invoice, $template);
        if (request()->boolean('download')) {
            return $this->download($invoice, $template, $filename);
        }

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    private function pdfBytes(Invoice $invoice, ?InvoiceTemplate $template): string
    {
        if (! class_exists(Mpdf::class)) {
            throw new RuntimeException('mPDF is not installed. Run composer install before serving invoice PDFs.');
        }

        $tempDir = storage_path('framework/cache/mpdf');
        File::ensureDirectoryExists($tempDir);
        foreach ([
            'ArbFONTS-Droid.Arabic.Kufi_DownloadSoftware.iR_.ttf',
            'ArbFONTS-Droid.Arabic.Kufi_.Bold_DownloadSoftware.iR_.ttf',
        ] as $font) {
            if (! is_readable(public_path('assets/fonts/'.$font))) {
                throw new RuntimeException("Required invoice font is not readable: {$font}");
            }
        }

        $data = $this->factory->make($invoice, $template);
        $presentation = $this->resolver->presentation($data->template);

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0,
            'tempDir' => $tempDir,
            'fontDir' => [public_path('assets/fonts')],
            'fontdata' => [
                'invoicearabic' => [
                    'R' => 'ArbFONTS-Droid.Arabic.Kufi_DownloadSoftware.iR_.ttf',
                    'B' => 'ArbFONTS-Droid.Arabic.Kufi_.Bold_DownloadSoftware.iR_.ttf',
                ],
            ],
            'default_font' => 'invoicearabic',
            'autoScriptToLang' => true,
            'autoLangToFont' => false,
            'useSubstitutions' => true,
        ]);
        $mpdf->SetTitle((string) ($invoice->invoice_number ?: 'Invoice'));
        $mpdf->SetAuthor((string) ($data->doc['company']['name'] ?? config('app.name')));
        $mpdf->SetDirectionality($data->direction);
        $mpdf->WriteHTML(view('company.invoice-templates.mpdf', [
            'data' => $data,
            'doc' => $data->doc,
            'presentation' => $presentation,
        ])->render());

        return $mpdf->Output('', Destination::STRING_RETURN);
    }

    private function renderHtml(Invoice $invoice, ?InvoiceTemplate $template = null): string
    {
        $data = $this->factory->make($invoice, $template);
        $presentation = $this->resolver->presentation($data->template);

        return view($data->template->view_path ?: 'company.invoice-templates.render.arabic-classic', [
            'data' => $data,
            'templatePresentation' => $presentation,
            'invoiceStylesheet' => null,
            'pdfRenderer' => 'browser',
        ])->render();
    }
}
