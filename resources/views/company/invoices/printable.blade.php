<!doctype html>
<html lang="ar" dir="rtl">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $invoice->invoice_number }}</title><link rel="stylesheet" href="{{ asset('css/invoice-document.css') }}?v={{ filemtime(public_path('css/invoice-document.css')) }}"></head>
<body class="invoice-document-body"><main class="print-preview-shell"><div class="invoice-print-page"><div class="invoice-print-content">@include('company.invoices.partials.document', ['doc' => app(\App\Services\Invoices\InvoiceDisplayDataFactory::class)->make($invoice), 'language' => 'ar', 'direction' => 'rtl'])</div></div></main><script src="{{ asset('js/invoice-print.js') }}?v={{ filemtime(public_path('js/invoice-print.js')) }}" defer></script></body>
</html>
