<!doctype html>
<html lang="ar" dir="rtl" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'InvoiceHub Jo')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="{{ asset('vendor/zaha-theme/css/theme.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/zaha-theme/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('css/phase1-layout.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="phase1-layout">
<div class="app-shell">
    <x-layout.sidebar />
    <div class="app-main">
        <x-layout.topbar />
        <main class="app-content">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
<script src="{{ asset('vendor/zaha-theme/js/app.js') }}" defer></script>
<script src="{{ asset('js/phase1-layout.js') }}" defer></script>
@stack('scripts')
</body>
</html>
