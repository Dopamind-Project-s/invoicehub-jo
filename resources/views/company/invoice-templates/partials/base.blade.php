@php($variant = $variant ?? 'classic')
<!doctype html>
<html lang="{{ str_contains($data->language, 'en') ? 'en' : 'ar' }}" dir="{{ $data->direction }}">
<head>
    <meta charset="utf-8">
    <title>{{ $data->invoice->invoice_number }}</title>
    <link rel="stylesheet" href="file://{{ public_path('css/invoice-document.css') }}">
</head>
<body class="invoice-document-body invoice-template-{{ $variant }}">
    <main class="invoice-shell">
        @include('company.invoices.partials.document', ['doc' => $data->doc])
        <footer class="invoice-footer">{{ $data->branding['footer_text'] }}</footer>
    </main>
</body>
</html>
