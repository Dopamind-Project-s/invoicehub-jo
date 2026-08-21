<?php

declare(strict_types=1);

namespace App\Services\Invoices;

use App\Models\Invoice;
use App\Models\InvoiceTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Throwable;

class InvoicePdfRenderer
{
    public function __construct(private readonly InvoiceTemplateDataFactory $factory, private readonly InvoiceTemplateResolver $resolver) {}

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
        if (class_exists(Mpdf::class)) {
            try {
                return $this->renderWithMpdf($invoice, $template);
            } catch (Throwable $exception) {
                Log::warning('mPDF invoice rendering failed; using the bundled PDF fallback.', [
                    'exception' => $exception,
                    'invoice_id' => $invoice->getKey(),
                    'renderer' => 'mpdf',
                ]);
            }
        } else {
            Log::warning('mPDF is not installed; using the bundled PDF fallback.', [
                'invoice_id' => $invoice->getKey(),
                'renderer' => 'dompdf',
            ]);
        }

        return $this->renderWithDompdf($invoice, $template);
    }

    private function renderWithMpdf(Invoice $invoice, ?InvoiceTemplate $template): string
    {
        $tempDir = storage_path('framework/cache/mpdf');
        File::ensureDirectoryExists($tempDir);

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
                'invoicenumeric' => [
                    // mPDF fontdata accepts TrueType/OpenType fonts, not the WOFF
                    // web-font files used by the browser preview.
                    'R' => 'ArbFONTS-Droid-Arabic-Kufi.ttf',
                    'B' => 'ArbFONTS-Droid.Arabic.Kufi_.Bold_DownloadSoftware.iR_.ttf',
                ],
            ],
            'default_font' => 'invoicearabic',
            'autoScriptToLang' => true,
            'autoLangToFont' => false,
        ]);
        $mpdf->SetDirectionality($invoice->language === 'en' ? 'ltr' : 'rtl');
        $mpdf->WriteHTML($this->renderHtml($invoice, $template, true, true));

        return $mpdf->Output('', Destination::STRING_RETURN);
    }

    private function renderWithDompdf(Invoice $invoice, ?InvoiceTemplate $template): string
    {
        $pdf = Pdf::loadHTML($this->renderHtml($invoice, $template, true, true))->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'DejaVu Sans',
            'chroot' => base_path(),
            'dpi' => 144,
            'isFontSubsettingEnabled' => true,
        ]);

        return $pdf->output();
    }

    private function renderHtml(Invoice $invoice, ?InvoiceTemplate $template = null, bool $embedAssets = false, bool $mpdf = false): string
    {
        $data = $this->factory->make($invoice, $template);
        $presentation = $this->resolver->presentation($data->template);

        return view($data->template->view_path ?: 'company.invoice-templates.render.arabic-classic', [
            'data' => $data,
            'templatePresentation' => $presentation,
            'invoiceStylesheet' => $embedAssets ? $this->embeddedStylesheet($presentation, $mpdf) : null,
            'pdfRenderer' => $mpdf ? 'mpdf' : 'browser',
        ])->render();
    }

    /** @param array<string, string> $presentation */
    private function embeddedStylesheet(array $presentation, bool $mpdf = false): string
    {
        $css = File::get(public_path('css/invoice-document.css'));
        $templateCss = public_path($presentation['stylesheet']);
        if (is_file($templateCss)) {
            $css .= "\n".File::get($templateCss);
        }
        if ($mpdf) {
            $css .= "\n".File::get(resource_path('css/invoice/mpdf.css'));
            $css = $this->resolveCssVariables($css);
            // The fonts are registered directly with mPDF above. Browser font-face
            // URLs (and their data-URI conversion below) are unnecessary and can
            // make the generated document substantially larger.
            $css = (string) preg_replace('/@font-face\s*\{[^}]*}/is', '', $css);
        }

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

    private function resolveCssVariables(string $css): string
    {
        preg_match_all('/--([a-z0-9-]+)\s*:\s*([^;}{]+)\s*;/i', $css, $matches, PREG_SET_ORDER);
        $variables = [];
        foreach ($matches as $match) {
            $variables[$match[1]] = trim($match[2]);
        }

        for ($pass = 0; $pass < 3; $pass++) {
            $css = (string) preg_replace_callback(
                '/var\(--([a-z0-9-]+)(?:\s*,\s*([^\)]+))?\)/i',
                static fn (array $match): string => $variables[$match[1]] ?? trim($match[2] ?? 'inherit'),
                $css,
            );
        }

        return (string) preg_replace('/--[a-z0-9-]+\s*:\s*[^;}{]+\s*;/i', '', $css);
    }
}
