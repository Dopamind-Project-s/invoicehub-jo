@extends('layouts.guest')
@section('title', 'تم استلام طلب الاشتراك')
@push('styles')<link rel="stylesheet" href="{{ asset('css/subscription-requests.css') }}">@endpush
@section('content')
<section class="sp" dir="rtl"><div class="container"><div class="subscription-request-card p-5 text-center"><div class="display-4 mb-3">✅</div><h1 class="h3 mb-3">شكرًا لك، تم استلام طلب الاشتراك وسيتم التواصل معك قريبًا.</h1><p class="text-muted mb-4">سيقوم فريق InvoSync / JoFotara بمراجعة البيانات والتواصل لإكمال تجهيز الاشتراك يدويًا.</p><a class="btn bgrd px-5 py-2" href="{{ route('home') }}">العودة للرئيسية</a> <a class="btn btn-outline-secondary rounded-pill px-4 py-2" href="{{ route('home') }}#pricing">عرض الباقات</a></div></div></section>
@endsection
