<!doctype html>
<html lang="ar" dir="rtl" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/bootstrap-rtl-lite.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Style.css') }}">
</head>
<body class="dir-rtl">
<main class="container py-5">
    <section class="card card-body col-lg-7 mx-auto text-center shadow-sm">
        <img src="{{ asset('assets/logos/logo2.svg') }}" alt="{{ config('app.name') }}" class="mx-auto mb-4" width="180">
        <div class="display-4 fw-bold text-primary">@yield('code')</div>
        <h1 class="h3 mt-3">@yield('heading')</h1>
        <p class="text-muted mt-2">@yield('message')</p>
        <div class="mt-3"><a class="btn btn-primary" href="{{ route('home') }}">العودة إلى الصفحة الرئيسية</a></div>
    </section>
</main>
</body>
</html>
