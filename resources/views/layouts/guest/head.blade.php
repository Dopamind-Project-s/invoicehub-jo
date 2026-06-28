<!DOCTYPE html>
<html lang="ar" dir="rtl" id="htmlRoot">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ data_get($settings ?? [], 'seo.description_ar', 'نظام فوترة إلكترونية عربي للمنشآت.') }}">
    <title>{{ trim($__env->yieldContent('title')) ?: data_get($settings ?? [], 'seo.title_ar', 'InvoSync | نظام فوترة إلكترونية للمنشآت') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/aos.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/swiper-bundle.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <style>:root{--pur:#0ea5e9;--cyan:#06b6d4;--teal:#14b8a6;--grad:linear-gradient(135deg,#0ea5e9,#06b6d4);--bg:#0f172a;--bg2:#111827;--bg3:#1e293b;--sf:#172033;--bd:rgba(148,163,184,.18);--bd2:rgba(14,165,233,.32);--tx:#f8fafc;--tx2:#cbd5e1;--tx3:#94a3b8}.lm{--bg:#f8fafc;--bg2:#eef6fb;--bg3:#ffffff;--sf:#ffffff;--bd:#dbeafe;--bd2:#bae6fd;--tx:#172033;--tx2:#475569;--tx3:#64748b}.gc,.pcard,.dwrap,.stpill{border-radius:24px;box-shadow:0 18px 42px rgba(15,23,42,.08)}.bgrd{background:var(--grad)!important;border:0!important;color:#fff!important}.boc{border:1px solid var(--bd2)!important;color:var(--pur)!important;background:var(--sf)!important}.gt{background:var(--grad);-webkit-background-clip:text;background-clip:text;color:transparent}.ftico{width:52px;height:52px;border-radius:16px;background:rgba(14,165,233,.12);display:flex;align-items:center;justify-content:center;margin-bottom:16px;color:var(--pur);font-weight:800}.pchk{color:var(--pur);margin-left:8px}.nav-link{color:var(--tx2)!important}.guest-main{min-height:48vh}.auth-page{padding:130px 0 80px}.auth-card{max-width:520px;margin:auto;background:var(--sf);border:1px solid var(--bd);border-radius:28px;box-shadow:0 24px 60px rgba(15,23,42,.16);padding:32px}.auth-card .form-control{background:var(--bg3);border:1px solid var(--bd);color:var(--tx);border-radius:14px;padding:12px 14px}.auth-card .form-label,.auth-card h1{color:var(--tx)}.auth-card a{color:var(--pur)}</style>
    @stack('styles')
</head>
