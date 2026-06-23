<!DOCTYPE html>
<html lang="ar" dir="rtl" id="htmlRoot">
   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <meta name="description" content="{{ data_get($settings ?? [], 'seo.description_ar', 'نظام فوترة إلكترونية عربي للمنشآت.') }}">
      <title>{{ data_get($settings ?? [], 'seo.title_ar', 'InvoSync | نظام فوترة إلكترونية للمنشآت') }}</title>
      <link rel="preconnect" href="https://fonts.googleapis.com">
      <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
      <!-- Bootstrap 5.3 -->
      <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet"/>
      <!-- AOS Animate on Scroll -->
      <link href="{{ asset('assets/css/aos.css') }}" rel="stylesheet"/>
      <!-- Swiper -->
      <link href="{{ asset('assets/css/swiper-bundle.min.css') }}" rel="stylesheet"/>
      <!-- all min css -->
      <link rel="stylesheet" href="{{ asset('assets/css/all.min.css') }}"/>
      <!-- magnific CSS -->
      <link rel="stylesheet" href="{{ asset('assets/css/magnific-popup.css') }}"/>
      <!-- Style CSS -->
      <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" />
   <style>:root{--pur:#0ea5e9;--cyan:#06b6d4;--teal:#14b8a6;--grad:linear-gradient(135deg,#0ea5e9,#06b6d4);--bg:#0f172a;--bg2:#111827;--bg3:#1e293b;--sf:#172033;--bd:rgba(148,163,184,.18);--bd2:rgba(14,165,233,.32);--tx:#f8fafc;--tx2:#cbd5e1;--tx3:#94a3b8}.lm{--bg:#f8fafc;--bg2:#eef6fb;--bg3:#ffffff;--sf:#ffffff;--bd:#dbeafe;--bd2:#bae6fd;--tx:#172033;--tx2:#475569;--tx3:#64748b}.gc,.pcard,.dwrap,.stpill{border-radius:24px;box-shadow:0 18px 42px rgba(15,23,42,.08)}.bgrd{background:var(--grad)!important;border:0!important;color:#fff!important}.boc{border:1px solid var(--bd2)!important;color:var(--pur)!important;background:var(--sf)!important}.gt{background:var(--grad);-webkit-background-clip:text;background-clip:text;color:transparent}.ftico{width:52px;height:52px;border-radius:16px;background:rgba(14,165,233,.12);display:flex;align-items:center;justify-content:center;margin-bottom:16px;color:var(--pur);font-weight:800}.pchk{color:var(--pur);margin-left:8px}.nav-link{color:var(--tx2)!important}</style>
   </head>
   <body>
      <!-- ======================== LANDING PAGE ======================== -->
      <div id="landing">
         @php
            $landingUrl = Route::has('home') ? route('home') : url('/');
            $isHome = request()->is('/');
            $sectionHref = fn (string $section): string => $isHome ? '#'.$section : $landingUrl.'#'.$section;
            $workspaceUrl = auth()->check() ? route('dashboard') : route('login');
         @endphp
         <!-- NAVBAR -->
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
                     <i class="fa-solid fa-sun" id="suni" style="display:none"></i>
                     <i class="fa-solid fa-moon" id="mooni"></i>
                     </button>
                     <a class="bgrd btn px-3 py-2 d-none d-sm-flex align-items-center gap-1" href="{{ $workspaceUrl }}">
                     <i class="fa-regular fa-user fa-sm"></i> {{ auth()->check() ? 'لوحة التحكم' : 'دخول' }}
                     </a>
                     <button class="boc d-lg-none px-2 py-2" id="mbtog" style="border-radius:10px">
                     <i class="fa-solid fa-bars" id="barIcon"></i>
                     <i class="fa-solid fa-xmark" id="xIcon" style="display:none"></i>
                     </button>
                  </div>
               </div>
               <div class="d-lg-none mt-3" id="mbmenu">
                  <div class="gc p-3 d-grid gap-2">
                     <a href="{{ $sectionHref('features') }}" class="nav-link">المزايا</a>
                     <a href="{{ $sectionHref('integrations') }}" class="nav-link">التكاملات</a>
                     <a href="{{ $sectionHref('pricing') }}" class="nav-link">الباقات</a>
                     <a href="{{ $sectionHref('faq') }}" class="nav-link">الأسئلة الشائعة</a>
                     <a class="bgrd btn w-100 py-2 mt-2" href="{{ $workspaceUrl }}">{{ auth()->check() ? 'لوحة التحكم' : 'دخول' }}</a>
                  </div>
               </div>
            </div>
         </nav>
