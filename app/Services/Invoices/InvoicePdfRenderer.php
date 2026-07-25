<?php

declare(strict_types=1);

namespace App\Services\Invoices;

use App\Models\Invoice;
use App\Models\InvoiceTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Spatie\Browsershot\Browsershot;
use Throwable;

class InvoicePdfRenderer
{
    public function __construct(private readonly InvoiceTemplateDataFactory $factory) {}

    public function html(Invoice $invoice, ?InvoiceTemplate $template = null): string
    {
        return $this->renderHtml($invoice, $template);
    }

    public function download(Invoice $invoice, ?InvoiceTemplate $template = null, ?string $filename = null): Response
    {
        $filename ??= ($invoice->invoice_number ?: 'invoice').'.pdf';
        try {
            $html = $this->renderHtml($invoice, $template, true);
            $pdf = $this->pdfBytes($html);
        } catch (RuntimeException $exception) {
            report($exception);

            return response('تعذر إنشاء ملف PDF بشكل صحيح. يرجى التحقق من توفر Chromium ثم المحاولة مرة أخرى.', 503, ['Content-Type' => 'text/plain; charset=UTF-8']);
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

    private function pdfBytes(string $html): string
    {
        try {
            if (class_exists(Browsershot::class)) {
                $browsershot = Browsershot::html($html)
                    ->format('A4')
                    ->margins(0, 0, 0, 0)
                    ->showBackground()
                    ->hideBrowserHeaderAndFooter()
                    ->waitUntilNetworkIdle()
                    ->waitForSelector('.invoice-document.invoice-page')
                    ->waitForFunction('document.fonts === undefined || document.fonts.status === "loaded"')
                    ->emulateMedia('print')
                    ->windowSize(1240, 1754)
                    ->deviceScaleFactor(1);

                if (filled(config('services.invoice_pdf.node_binary'))) {
                    $browsershot->setNodeBinary((string) config('services.invoice_pdf.node_binary'));
                }
                if (filled(config('services.invoice_pdf.chrome_path'))) {
                    $browsershot->setChromePath((string) config('services.invoice_pdf.chrome_path'));
                }

                return $browsershot->pdf();
            }
        } catch (Throwable $exception) {
            Log::error('Chromium invoice PDF rendering failed.', ['exception' => $exception, 'renderer' => 'browsershot']);

            if (! app()->environment(['local', 'testing']) && ! config('services.invoice_pdf.allow_dompdf_fallback', false)) {
                throw new RuntimeException('Chromium is required to render Arabic invoice PDFs.', previous: $exception);
            }
        }

        File::ensureDirectoryExists(storage_path('fonts'));
        $pdf = Pdf::loadHTML($html)->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'InvoiceArabic',
            'fontDir' => storage_path('fonts'),
            'fontCache' => storage_path('fonts'),
            'chroot' => base_path(),
            'dpi' => 144,
            'isFontSubsettingEnabled' => true,
        ]);

        return $pdf->output();
    }

    private function renderHtml(Invoice $invoice, ?InvoiceTemplate $template = null, bool $embedAssets = false): string
    {
        $data = $this->factory->make($invoice, $template);

        return view($data->template->view_path ?: 'company.invoice-templates.render.arabic-classic', [
            'data' => $data,
            'invoiceStylesheet' => $embedAssets ? $this->embeddedStylesheet() : null,
        ])->render();
    }

    private function embeddedStylesheet(): string
    {
        $css = File::get(public_path('css/invoice-document.css'));

        return (string) preg_replace_callback(
            '~url\([\'\"]?\.\./assets/fonts/([^\'\")]+)[\'\"]?\)~',
            static function (array $match): string {
                $path = public_path('assets/fonts/'.basename($match[1]));
                if (! is_file($path) || ! is_readable($path)) {
                    return $match[0];
                }

                $mime = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
                    'woff2' => 'font/woff2',
                    'woff' => 'font/woff',
                    'otf' => 'font/otf',
                    default => 'font/ttf',
                };

                return 'url("data:'.$mime.';base64,'.base64_encode((string) file_get_contents($path)).'")';
            },
            $css,
        );
    }
}
