@php($variant = $variant ?? 'classic')
<!doctype html>
<html lang="{{ str_contains($data->language, 'en') ? 'en' : 'ar' }}" dir="{{ $data->direction }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $data->invoice->invoice_number }}</title>
    @if(isset($invoiceStylesheet))
        <style>{!! $invoiceStylesheet !!}</style>
    @else
        <link rel="stylesheet" href="{{ asset('css/invoice-document.css') }}?v={{ filemtime(public_path('css/invoice-document.css')) }}">
        <link rel="stylesheet" href="{{ asset($templatePresentation['stylesheet']) }}?v={{ filemtime(public_path($templatePresentation['stylesheet'])) }}">
    @endif
</head>
<body class="invoice-document-body {{ $templatePresentation['root_class'] }} {{ ($pdfRenderer ?? null) === 'dompdf' ? 'invoice-pdf-dompdf' : '' }}"
      data-template="{{ $templatePresentation['slug'] }}"
      data-layout="{{ $templatePresentation['layout'] }}"
      data-header="{{ $templatePresentation['header'] }}"
      data-info="{{ $templatePresentation['info'] }}"
      data-table="{{ $templatePresentation['table'] }}"
      data-totals="{{ $templatePresentation['totals'] }}"
      data-qr="{{ $templatePresentation['qr'] }}">
    <main class="print-preview-shell">
        <div class="invoice-print-page">
            <div class="invoice-print-content">
                @include('company.invoices.partials.document', ['doc' => $data->doc, 'language' => $data->language, 'direction' => $data->direction])
                <footer class="invoice-footer">{{ $data->branding['footer_text'] }}</footer>
            </div>
        </div>
    </main>
    <script src="{{ asset('js/invoice-print.js') }}?v={{ filemtime(public_path('js/invoice-print.js')) }}" defer></script>
</body>
</html>
