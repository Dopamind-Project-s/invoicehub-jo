<!DOCTYPE html>
<html lang="en" id="htmlRoot">
   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>InvoSync Jo - Automate Everything with AI Agents</title>
      <link rel="preconnect" href="https://fonts.googleapis.com">
      <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
      <!-- Bootstrap 5.3 -->
      <link href="assets/css/bootstrap.min.css" rel="stylesheet"/>
      <!-- AOS Animate on Scroll -->
      <link href="assets/css/aos.css" rel="stylesheet"/>
      <!-- Swiper -->
      <link href="assets/css/swiper-bundle.min.css" rel="stylesheet"/>
      <!-- all min css -->
      <link rel="stylesheet" href="assets/css/all.min.css"/>
      <!-- magnific CSS -->
      <link rel="stylesheet" href="assets/css/magnific-popup.css"/>
      <!-- Style CSS -->
      <link rel="stylesheet" href="assets/css/style.css" />
   </head>
   <body>
      <!-- ======================== LANDING PAGE ======================== -->
      <div id="landing">
         <!-- NAVBAR -->
         <nav id="nbar">
            <div class="container">
               <div class="d-flex align-items-center justify-content-between w-100">
                  <a href="#" class="d-flex align-items-center gap-2" style="font-size:1.2rem;font-weight:700;color:var(--tx)">
                     <div class="logo-i"><img src="assets/logos/logo.svg" alt="InvoSync Jo" style="width:50px;height:50px"></div>
                     <span>InvoSync Jo</span>
                  </a>
                  <div class="d-none d-lg-flex align-items-center gap-1 mx-auto">
                     <a href="#features" class="nav-link">Features</a>
                     <a href="#integrations" class="nav-link">Integrations</a>
                     <a href="#pricing" class="nav-link">Pricing</a>
                     <a href="#faq" class="nav-link">FAQ</a>
                  </div>
                  <div class="d-flex align-items-center gap-2">
                     <button class="boc d-flex align-items-center justify-content-center" id="thbtn" style="width:38px;height:38px;padding:0;border-radius:12px" aria-label="Toggle theme">
                     <i class="fa-solid fa-sun" id="suni" style="display:none"></i>
                     <i class="fa-solid fa-moon" id="mooni"></i>
                     </button>
                     <button class="boc px-3 py-2 d-none d-sm-flex align-items-center gap-1" data-bs-toggle="offcanvas" data-bs-target="#lofc" onclick="swTab('login')">
                     <i class="fa-regular fa-user fa-sm"></i> Log in
                     </button>
                     <button class="bgrd btn px-3 py-2 d-none d-sm-flex align-items-center gap-1" data-bs-toggle="offcanvas" data-bs-target="#lofc" onclick="swTab('signup')">
                     Get Started <i class="fa-solid fa-arrow-right fa-sm"></i>
                     </button>
                     <button class="boc d-lg-none px-2 py-2" id="mbtog" style="border-radius:10px">
                     <i class="fa-solid fa-bars" id="barIcon"></i>
                     <i class="fa-solid fa-xmark" id="xIcon" style="display:none"></i>
                     </button>
                  </div>
               </div>
            </div>
         </nav>