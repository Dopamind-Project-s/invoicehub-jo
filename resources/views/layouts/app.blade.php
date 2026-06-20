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
    @stack('styles')
</head>
<body class="dir-rtl">
<div class="layout-shell">
    <aside id="appSidebar" class="sidebar-original">
        <div class="sidebar-brand">
            <a href="{{ url('/') }}" class="d-flex align-items-center gap-2 text-decoration-none">
                <img class="brand-logo" src="{{ asset('assets/logos/logo2.svg') }}" alt="InvoSync Jo">
                <span class="brand-title">InvoSync Jo</span>
            </a>
        </div>
        <p class="side-comment">القائمة الرئيسية</p>
        <ul class="side-list">
            <li class="side-item {{ request()->routeIs('dashboard') ? 'selected' : '' }}"><a href="{{ route('dashboard') }}"><span>🏠</span><span>لوحة التحكم</span></a></li>
            @auth
                @if(auth()->user()->isSuperAdmin())
                    <li class="side-item {{ request()->routeIs('admin.dashboard') ? 'selected' : '' }}"><a href="{{ route('admin.dashboard') }}"><span>📊</span><span>لوحة المدير العام</span></a></li>
                    <li class="side-item {{ request()->routeIs('admin.companies.*') ? 'selected' : '' }}"><a href="{{ route('admin.companies.index') }}"><span>🏢</span><span>المنشآت</span></a></li>
                    <li class="side-item {{ request()->routeIs('admin.feature-keys.*') ? 'selected' : '' }}"><a href="{{ route('admin.feature-keys.index') }}"><span>🧩</span><span>مفاتيح المزايا</span></a></li>
                    <li class="side-item {{ request()->routeIs('admin.plans.*') ? 'selected' : '' }}"><a href="{{ route('admin.plans.index') }}"><span>💳</span><span>الباقات</span></a></li>
                @endif
            @endauth
            @if(request()->route('company'))
                @php($layoutCompany = request()->route('company'))
                <li class="side-item {{ request()->routeIs('company.users.*') ? 'selected' : '' }}"><a href="{{ route('company.users.index', $layoutCompany) }}"><span>👥</span><span>المستخدمون</span></a></li>
                <li class="side-item {{ request()->routeIs('company.roles.*') ? 'selected' : '' }}"><a href="{{ route('company.roles.index', $layoutCompany) }}"><span>🔐</span><span>الأدوار</span></a></li>
                <li class="side-item {{ request()->routeIs('company.dashboard') ? 'selected' : '' }}"><a href="{{ route('company.dashboard', $layoutCompany) }}"><span>🏠</span><span>لوحة المنشأة</span></a></li>
                <li class="side-item {{ request()->routeIs('company.products.*') ? 'selected' : '' }}"><a href="{{ route('company.products.index', $layoutCompany) }}"><span>📦</span><span>المنتجات</span></a></li>
                <li class="side-item {{ request()->routeIs('company.product-categories.*') ? 'selected' : '' }}"><a href="{{ route('company.product-categories.index', $layoutCompany) }}"><span>🗂</span><span>التصنيفات</span></a></li>
                <li class="side-item {{ request()->routeIs('company.units.*') ? 'selected' : '' }}"><a href="{{ route('company.units.index', $layoutCompany) }}"><span>📏</span><span>الوحدات</span></a></li>
                <li class="side-item {{ request()->routeIs('company.tax-profiles.*') ? 'selected' : '' }}"><a href="{{ route('company.tax-profiles.index', $layoutCompany) }}"><span>🧮</span><span>الضرائب</span></a></li>
                <li class="side-item {{ request()->routeIs('company.contacts.*') ? 'selected' : '' }}"><a href="{{ route('company.contacts.index', $layoutCompany) }}"><span>🤝</span><span>العملاء والموردون</span></a></li>
                <li class="side-item {{ request()->routeIs('company.invoices.*') ? 'selected' : '' }}"><a href="{{ route('company.invoices.index', $layoutCompany) }}"><span>🧾</span><span>الفواتير</span></a></li>
                <li class="side-item {{ request()->routeIs('company.invoice-templates.*') ? 'selected' : '' }}"><a href="{{ route('company.invoice-templates.index', $layoutCompany) }}"><span>🧾</span><span>قوالب الفواتير</span></a></li>
                <li class="side-item {{ request()->routeIs('company.settings.*') ? 'selected' : '' }}"><a href="{{ route('company.settings.edit', $layoutCompany) }}"><span>⚙️</span><span>الإعدادات</span></a></li>
                <li class="side-item {{ request()->routeIs('company.activity.*') ? 'selected' : '' }}"><a href="{{ route('company.activity.index', $layoutCompany) }}"><span>📝</span><span>النشاط</span></a></li>
            @endif
        </ul>
    </aside>

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
            @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
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
@stack('scripts')
</body>
</html>
