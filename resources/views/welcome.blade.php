<!doctype html>
<html lang="ar" dir="rtl" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'InvoSync Jo') }}</title>
    <link rel="stylesheet" href="{{ asset('css/bootstrap-rtl-lite.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/phase1-layout.css') }}">
</head>
<body class="dir-rtl">
<div class="layout-shell">
    <div class="content-shell" style="margin-inline:0;">
        <header class="container py-4">
            <nav class="navbar topbar-original topbar-pill">
                <a href="{{ url('/') }}" class="d-flex align-items-center gap-2 text-decoration-none">
                    <img class="brand-logo" src="{{ asset('assets/logos/logo2.svg') }}" alt="InvoSync Jo">
                    <span class="brand-title">InvoSync Jo</span>
                </a>
                <ul class="nav ms-auto align-items-center gap-2">
                    @auth
                        <li><a class="btn btn-primary" href="{{ route('dashboard') }}">لوحة التحكم</a></li>
                    @else
                        <li><a class="btn btn-outline-primary" href="{{ route('login') }}">تسجيل الدخول</a></li>
                        @if (Route::has('register'))<li><a class="btn btn-primary" href="{{ route('register') }}">إنشاء حساب</a></li>@endif
                    @endauth
                </ul>
            </nav>
        </header>
        <main class="container content-main">
            <section class="card p-4 mb-4">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-7">
                        <span class="badge bg-primary-subtle text-primary border mb-3">Arabic-first SaaS invoicing</span>
                        <h1 class="h1 mb-3">منصة موحدة لإدارة الفوترة الإلكترونية للمنشآت الأردنية</h1>
                        <p class="text-muted mb-4">واجهة عربية RTL مبنية على أصول الثيم العامة، بدون Vite أو manifest، ومتصلة بلوحات الإدارة ومساحات المنشآت الحالية.</p>
                        <div class="d-flex gap-2 flex-wrap">
                            @auth
                                <a class="btn btn-primary btn-lg" href="{{ route('dashboard') }}">الانتقال للوحة التحكم</a>
                            @else
                                <a class="btn btn-primary btn-lg" href="{{ route('login') }}">ابدأ بتسجيل الدخول</a>
                            @endauth
                            <a class="btn btn-outline-secondary btn-lg" href="#features">استعراض الأساسيات</a>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="kpi-card stat-card-blue"><div class="kpi-head"><p class="kpi-label">حالة المنصة</p><span class="kpi-icon">✅</span></div><h3 class="kpi-value">مستقرة</h3><p class="kpi-delta up">جاهزة لاستكمال التحقق</p></div>
                    </div>
                </div>
            </section>
            <section id="features" class="row g-3 mb-4">
                <div class="col-md-4"><div class="stat-card"><div class="stat-icon">🏢</div><div><span>إدارة المنشآت</span><strong>Super Admin</strong></div></div></div>
                <div class="col-md-4"><div class="stat-card"><div class="stat-icon">🔐</div><div><span>صلاحيات معزولة</span><strong>Spatie Teams</strong></div></div></div>
                <div class="col-md-4"><div class="stat-card"><div class="stat-icon">🧾</div><div><span>محرك الفواتير</span><strong>V1</strong></div></div></div>
            </section>
        </main>
        <footer class="app-footer">© {{ date('Y') }} InvoSync Jo</footer>
    </div>
</div>
<script src="{{ asset('js/app.js') }}" defer></script>
</body>
</html>
