@php
    $landingUrl = Route::has('home') ? route('home') : url('/');
    $isHome = request()->is('/');
    $sectionHref = fn (string $section): string => $isHome ? '#'.$section : $landingUrl.'#'.$section;
    $workspaceUrl = auth()->check() ? route('dashboard') : route('login');
    $loginOffcanvasAttrs = 'data-bs-toggle="offcanvas" data-bs-target="#lofc" aria-controls="lofc"';
@endphp
<nav id="nbar">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between w-100">
            <a href="{{ $landingUrl }}" class="d-flex align-items-center gap-2" style="font-size:1.2rem;font-weight:700;color:var(--tx)">
                <div class="logo-i"><img src="{{ asset('assets/logos/logo.svg') }}" alt="InvoSync Jo" style="width:50px;height:50px"></div>
                <span>InvoSync Jo</span>
            </a>
            <div class="d-none d-lg-flex align-items-center gap-1 mx-auto">
                <a href="{{ $sectionHref('features') }}" class="nav-link">المزايا</a>
                <a href="{{ $sectionHref('integrations') }}" class="nav-link">التكاملات</a>
                <a href="{{ $sectionHref('pricing') }}" class="nav-link">الباقات</a>
                <a href="{{ $sectionHref('faq') }}" class="nav-link">الأسئلة الشائعة</a>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="boc d-flex align-items-center justify-content-center" id="thbtn" style="width:38px;height:38px;padding:0;border-radius:12px" aria-label="Toggle theme">
                    <i class="fa-solid fa-sun" id="suni" style="display:none"></i><i class="fa-solid fa-moon" id="mooni"></i>
                </button>
                @auth
                    <a class="bgrd btn px-3 py-2 d-none d-sm-flex align-items-center gap-1" href="{{ $workspaceUrl }}"><i class="fa-solid fa-gauge-high fa-sm"></i> لوحة التحكم</a>
                @else
                    <button class="boc btn px-3 py-2 d-none d-sm-flex align-items-center gap-1" type="button" {!! $loginOffcanvasAttrs !!}><i class="fa-regular fa-user fa-sm"></i> دخول</button>
                    <a class="bgrd btn px-3 py-2 d-none d-sm-flex align-items-center gap-1" href="{{ route('login') }}">ابدأ الآن <i class="fa-solid fa-arrow-left fa-sm"></i></a>
                @endauth
                <button class="boc d-lg-none px-2 py-2" id="mbtog" style="border-radius:10px"><i class="fa-solid fa-bars" id="barIcon"></i><i class="fa-solid fa-xmark" id="xIcon" style="display:none"></i></button>
            </div>
        </div>
        <div class="d-lg-none mt-3" id="mbmenu">
            <div class="gc p-3 d-grid gap-2">
                <a href="{{ $sectionHref('features') }}" class="nav-link">المزايا</a>
                <a href="{{ $sectionHref('integrations') }}" class="nav-link">التكاملات</a>
                <a href="{{ $sectionHref('pricing') }}" class="nav-link">الباقات</a>
                <a href="{{ $sectionHref('faq') }}" class="nav-link">الأسئلة الشائعة</a>
                @auth
                    <a class="bgrd btn w-100 py-2 mt-2" href="{{ $workspaceUrl }}">لوحة التحكم</a>
                @else
                    <button class="boc btn w-100 py-2 mt-2" type="button" {!! $loginOffcanvasAttrs !!}>دخول</button>
                    <a class="bgrd btn w-100 py-2" href="{{ route('login') }}">ابدأ الآن</a>
                @endauth
            </div>
        </div>
    </div>
</nav>
