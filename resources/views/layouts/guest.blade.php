<!doctype html>
<html lang="ar" dir="rtl" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'InvoSync Jo'))</title>
    <link rel="stylesheet" href="{{ asset('css/bootstrap-rtl-lite.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Style.css') }}">
    @stack('styles')
</head>
<body>
<main class="auth-layout container-fluid">
    <div class="row min-vh-100 align-items-center justify-content-center">
        <div class="col-12 col-md-10 col-lg-8 col-xl-6">
            <div class="card auth-card shadow-lg border-0">
                <div class="row g-0">
                    <div class="col-lg-5 d-none d-lg-flex auth-cover">
                        <div>
                            <h2 class="h4 mb-3">InvoSync Jo</h2>
                            <p class="mb-0">منصة فوترة إلكترونية عربية متوافقة مع متطلبات المنشآت الأردنية.</p>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="card-body p-4 p-md-5">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <a href="{{ url('/') }}" class="d-flex align-items-center gap-2 text-decoration-none">
                                    <img src="{{ asset('assets/logos/logo2.svg') }}" alt="InvoSync Jo" class="auth-logo">
                                    <div><h1 class="h5 mb-0">InvoSync Jo</h1><small class="text-muted">لوحة إدارة الفوترة</small></div>
                                </a>
                                <button class="btn btn-sm btn-outline-secondary" id="themeToggle" type="button">🌙 داكن</button>
                            </div>
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script src="{{ asset('js/app.js') }}" defer></script>
@stack('scripts')
</body>
</html>
