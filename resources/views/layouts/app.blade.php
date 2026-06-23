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
    <link rel="stylesheet" href="{{ asset('css/phase1-layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/internal-extracted.css') }}">
    @stack('styles')
</head>
<body class="dir-rtl">
<div class="layout-shell">
    <x-layout.sidebar :company="request()->route('company') ?: auth()->user()?->company" />

    <div class="content-shell">
        <header>
            <nav class="navbar topbar-original topbar-pill">
                <button id="sidebarToggle" class="btn topbar-toggle mobile-only" type="button">☰</button>
                <div class="topbar-title">@yield('page_title', trim($__env->yieldContent('title')) ?: config('app.name', 'InvoSync Jo'))</div>
                <ul class="nav ms-auto align-items-center gap-2 topbar-actions">
                    <li class="nav-item"><button id="themeToggle" class="btn btn-sm btn-outline-secondary" type="button">🌙 داكن</button></li>
                    @auth
                        <li class="nav-item"><span class="top-avatar top-avatar-icon">{{ mb_substr(auth()->user()->name ?? 'U', 0, 1) }}</span></li>
                        <li class="nav-item"><span class="btn btn-profile">{{ auth()->user()->name }}</span></li>
                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-sm btn-outline-secondary" type="submit">خروج</button></form>
                        </li>
                    @else
                        <li class="nav-item"><a class="btn btn-sm btn-primary" href="{{ route('login') }}">دخول</a></li>
                    @endauth
                </ul>
            </nav>
        </header>

        <main class="content-main">
            @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
            @if(isset($errors) && $errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            @isset($header)<div class="card card-body mb-3">{{ $header }}</div>@endisset
            @hasSection('content')
                @yield('content')
            @else
                {{ $slot ?? '' }}
            @endif
        </main>
        <footer class="app-footer">© {{ date('Y') }} InvoSync Jo</footer>
    </div>
</div>
<script src="{{ asset('js/app.js') }}" defer></script>
<script src="{{ asset('js/phase1-layout.js') }}" defer></script>
<script src="{{ asset('js/internal-extracted.js') }}" defer></script>
@stack('scripts')
</body>
</html>
