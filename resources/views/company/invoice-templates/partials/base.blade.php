@php($variant = $variant ?? 'classic')
<!doctype html>
<html lang="{{ str_contains($data->language, 'en') ? 'en' : 'ar' }}" dir="{{ $data->direction }}">
<head>
    <meta charset="utf-8">
    <title>{{ $data->invoice->invoice_number }}</title>
    @if(isset($invoiceStylesheet))
        <style>{!! $invoiceStylesheet !!}</style>
    @else
        <link rel="stylesheet" href="{{ asset('css/invoice-document.css') }}?v={{ filemtime(public_path('css/invoice-document.css')) }}">
    @endif
</head>
<body class="invoice-document-body invoice-template-{{ $variant }}">
    <main class="print-preview-shell">
        <div class="invoice-print-page">
            @include('company.invoices.partials.document', ['doc' => $data->doc, 'language' => $data->language, 'direction' => $data->direction])
            <footer class="invoice-footer">{{ $data->branding['footer_text'] }}</footer>
        </div>
    </main>
</body>
</html>
