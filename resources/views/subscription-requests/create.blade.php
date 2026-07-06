@extends('layouts.guest')
@section('title', 'طلب الاشتراك')
@push('styles')<link rel="stylesheet" href="{{ asset('css/subscription-requests.css') }}">@endpush
@section('content')
<section class="sp" dir="rtl">
    <div class="container">
        <div class="subscription-request-hero mb-4"><span class="badge bg-white text-primary mb-2">طلب اشتراك جديد</span><h1 class="h3 mb-2">أكمل بياناتك وسنتواصل معك قريبًا</h1><p class="mb-0 opacity-75">لا تحتاج إلى تسجيل دخول. سيقوم فريق الإدارة بمراجعة الطلب وتجهيز الاشتراك يدويًا.</p></div>
        <div class="subscription-request-card p-4 p-lg-5">
            <div class="mb-4"><span class="subscription-plan-pill">الباقة المختارة: {{ $plan->name_ar ?: $plan->name }}</span></div>
            @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <form method="post" action="{{ route('subscription-requests.store') }}" class="row g-3">
                @csrf
                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                <div class="col-md-6"><label class="form-label">اسم المنشأة / الشركة</label><input class="form-control" name="company_name" value="{{ old('company_name') }}" required></div>
                <div class="col-md-6"><label class="form-label">اسم مقدم الطلب</label><input class="form-control" name="applicant_name" value="{{ old('applicant_name') }}" required></div>
                <div class="col-md-6"><label class="form-label">البريد الإلكتروني</label><input type="email" class="form-control" name="email" value="{{ old('email') }}" required></div>
                <div class="col-md-6"><label class="form-label">رقم الهاتف</label><input class="form-control" name="phone" value="{{ old('phone') }}" required></div>
                <div class="col-md-6"><label class="form-label">رقم واتساب اختياري</label><input class="form-control" name="whatsapp" value="{{ old('whatsapp') }}"></div>
                <div class="col-md-3"><label class="form-label">الباقة المختارة</label><input class="form-control" value="{{ $plan->name_ar ?: $plan->name }}" readonly></div>
                <div class="col-md-3"><label class="form-label">مدة الاشتراك</label><select class="form-select" name="billing_cycle" required><option value="monthly" @selected(old('billing_cycle')==='monthly')>شهري</option><option value="yearly" @selected(old('billing_cycle','yearly')==='yearly')>سنوي</option></select></div>
                <div class="col-12"><label class="form-label">ملاحظات اختيارية</label><textarea class="form-control" name="notes" rows="4">{{ old('notes') }}</textarea></div>
                <div class="col-12 d-flex flex-wrap gap-2"><button class="btn bgrd px-5 py-2" type="submit">إرسال الطلب</button><a class="btn btn-outline-secondary rounded-pill px-4" href="{{ route('home') }}#pricing">العودة للباقات</a></div>
            </form>
        </div>
    </div>
</section>
@endsection
