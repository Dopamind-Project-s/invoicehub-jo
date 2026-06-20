<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'InvoSync Jo') }}</title>

        <link rel="stylesheet" href="{{ asset('css/Theme.css') }}">
        <link rel="stylesheet" href="{{ asset('css/Style.css') }}">
        <script src="{{ asset('js/app.js') }}" defer></script>
    </head>
    <body class="bg-light min-vh-100 d-flex align-items-center justify-content-center p-3">
        <main class="auth-shell w-100" style="max-width: 460px;">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <a href="{{ url('/') }}" class="d-inline-flex align-items-center justify-content-center mb-3">
                            <img src="{{ asset('assets/logos/logo.svg') }}" alt="InvoSync Jo" style="height: 64px; max-width: 180px;">
                        </a>
                    </div>

                    {{ $slot }}
                </div>
            </div>
        </main>
    </body>
</html>
