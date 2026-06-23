<!DOCTYPE html>
<html lang="ar" dir="rtl" id="htmlRoot">
   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <meta name="description" content="{{ ($settings ?? collect())->get('seo.description_ar', 'نظام فوترة إلكترونية عربي للمنشآت.') }}">
      <title>{{ ($settings ?? collect())->get('seo.title_ar', 'InvoSync | نظام فوترة إلكترونية للمنشآت') }}</title>
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
   <style>:root{--pur:#00a9c4;--cyan:#12c2b2;--grad:linear-gradient(135deg,#00a9c4,#12c2b2);--bg:#f8fdff;--bg2:#eef9fb;--bg3:#ffffff;--sf:#ffffff;--bd:#d7eef3;--bd2:#bfe7ee;--tx:#172033;--tx2:#475569;--tx3:#64748b}.gc,.pcard,.dwrap,.stpill{border-radius:24px;box-shadow:0 18px 42px rgba(15,23,42,.08)}.bgrd{background:var(--grad)!important;border:0!important;color:#fff!important}.boc{border:1px solid var(--bd2)!important;color:#0f6170!important;background:#fff!important}.gt{background:var(--grad);-webkit-background-clip:text;background-clip:text;color:transparent}.ftico{width:52px;height:52px;border-radius:16px;background:rgba(0,169,196,.12);display:flex;align-items:center;justify-content:center;margin-bottom:16px;color:#00a9c4;font-weight:800}.pchk{color:#00a9c4;margin-left:8px}.nav-link{color:var(--tx2)!important}</style>
   </head>
   <body>
      <!-- ======================== LANDING PAGE ======================== -->
      <div id="landing">
         <!-- NAVBAR -->
         <nav id="nbar">
            <div class="container">
               <div class="d-flex align-items-center justify-content-between w-100">
                  <a href="#" class="d-flex align-items-center gap-2" style="font-size:1.2rem;font-weight:700;color:var(--tx)">
                     <div class="logo-i"><img src="{{ asset('assets/logos/logo.svg') }}" alt="InvoSync Jo" style="width:50px;height:50px"></div>
                     <span>InvoSync Jo</span>
                  </a>
                  <div class="d-none d-lg-flex align-items-center gap-1 mx-auto">
                     <a href="#features" class="nav-link">المزايا</a>
                     <a href="#integrations" class="nav-link">التكاملات</a>
                     <a href="#pricing" class="nav-link">الباقات</a>
                     <a href="#faq" class="nav-link">الأسئلة الشائعة</a>
                  </div>
                  <div class="d-flex align-items-center gap-2">
                     <button class="boc d-flex align-items-center justify-content-center" id="thbtn" style="width:38px;height:38px;padding:0;border-radius:12px" aria-label="Toggle theme">
                     <i class="fa-solid fa-sun" id="suni" style="display:none"></i>
                     <i class="fa-solid fa-moon" id="mooni"></i>
                     </button>
                     <button class="boc px-3 py-2 d-none d-sm-flex align-items-center gap-1" data-bs-toggle="offcanvas" data-bs-target="#lofc" onclick="swTab('login')">
                     <i class="fa-regular fa-user fa-sm"></i> دخول
                     </button>
                     <button class="bgrd btn px-3 py-2 d-none d-sm-flex align-items-center gap-1" data-bs-toggle="offcanvas" data-bs-target="#lofc" onclick="swTab('signup')">
                     ابدأ الآن <i class="fa-solid fa-arrow-right fa-sm"></i>
                     </button>
                     <button class="boc d-lg-none px-2 py-2" id="mbtog" style="border-radius:10px">
                     <i class="fa-solid fa-bars" id="barIcon"></i>
                     <i class="fa-solid fa-xmark" id="xIcon" style="display:none"></i>
                     </button>
                  </div>
               </div>
            </div>
         </nav>