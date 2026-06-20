<!doctype html>
<html lang="ar" dir="rtl" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'InvoSync Jo')</title>
    <link rel="stylesheet" href="{{ asset('css/bootstrap-rtl-lite.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/phase1-layout.css') }}">
    @stack('styles')
</head>
<body class="dir-rtl">
<div class="layout-shell">
    <aside id="appSidebar" class="sidebar-original">
        <div class="sidebar-brand"><a href="{{ url('/') }}" class="d-flex align-items-center gap-2 text-decoration-none"><img class="brand-logo" src="{{ asset('assets/logos/logo2.svg') }}" alt="InvoSync Jo"><span class="brand-title">InvoSync Jo</span></a></div>
        <p class="side-comment">مساحة المنشأة</p>
        <ul class="side-list">
            @if(isset($company))
                <li class="side-item {{ request()->routeIs('company.invoices.*') ? 'selected' : '' }}"><a href="{{ route('company.invoices.index', $company) }}"><span>🧾</span><span>الفواتير</span></a></li>
                <li class="side-item {{ request()->routeIs('company.products.*') ? 'selected' : '' }}"><a href="{{ route('company.products.index', $company) }}"><span>📦</span><span>المنتجات</span></a></li>
                <li class="side-item {{ request()->routeIs('company.contacts.*') ? 'selected' : '' }}"><a href="{{ route('company.contacts.index', $company) }}"><span>🤝</span><span>جهات الاتصال</span></a></li>
                <li class="side-item {{ request()->routeIs('company.settings.*') ? 'selected' : '' }}"><a href="{{ route('company.settings.edit', $company) }}"><span>⚙️</span><span>الإعدادات</span></a></li>
            @endif
        </ul>
    </aside>
    <div class="content-shell">
        <header><nav class="navbar topbar-original topbar-pill"><button id="sidebarToggle" class="btn topbar-toggle mobile-only" type="button">☰</button><div class="topbar-title">@yield('title', 'مساحة المنشأة')</div><ul class="nav ms-auto align-items-center gap-2 topbar-actions"><li class="nav-item"><button id="themeToggle" class="btn btn-sm btn-outline-secondary" type="button">🌙 داكن</button></li></ul></nav></header>
        <main class="content-main">
            @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            @yield('content')
        </main>
        <footer class="app-footer">© {{ date('Y') }} InvoSync Jo</footer>
    </div>
</div>
<script src="{{ asset('js/app.js') }}" defer></script>
<script src="{{ asset('js/phase1-layout.js') }}" defer></script>
@stack('scripts')
</body>
</html>
