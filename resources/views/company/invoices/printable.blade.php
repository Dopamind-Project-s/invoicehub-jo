<!doctype html>
<html lang="ar" dir="rtl">
<head><meta charset="utf-8"><title>{{ $invoice->invoice_number }}</title><link rel="stylesheet" href="{{ asset('css/invoice-document.css') }}?v={{ filemtime(public_path('css/invoice-document.css')) }}"></head>
<body class="invoice-document-body"><main class="print-preview-shell"><div class="invoice-print-page">@include('company.invoices.partials.document', ['doc' => app(\App\Services\Invoices\InvoiceDisplayDataFactory::class)->make($invoice), 'language' => 'ar', 'direction' => 'rtl'])</div></main></body>
</html>
