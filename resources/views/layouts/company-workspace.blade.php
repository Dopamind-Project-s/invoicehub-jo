<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'InvoiceHub Jo')</title>
    <link rel="stylesheet" href="{{ asset('vendor/zaha-theme/css/Style.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/zaha-theme/css/Theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/phase1-layout.css') }}">
</head>
<body class="app-shell" dir="rtl">
    <div class="app-main">
        <main class="app-content container py-4">
            @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            @yield('content')
        </main>
    </div>
    <script src="{{ asset('js/phase1-layout.js') }}" defer></script>
</body>
</html>
