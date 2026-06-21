<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
            <div>
                <h1 class="h4 mb-2">لوحة التحكم</h1>
                <p class="text-muted mb-0">مرحباً بك في InvoSync Jo.</p>
            </div>
        </div>
    </x-slot>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="stat-card"><div class="stat-icon">🏢</div><div><span>المنشأة</span><strong>{{ auth()->user()->company_id ? 'مرتبطة' : 'غير محددة' }}</strong></div></div></div>
        <div class="col-md-4"><div class="stat-card"><div class="stat-icon">🔐</div><div><span>الحساب</span><strong>{{ auth()->user()->status ?? 'active' }}</strong></div></div></div>
        <div class="col-md-4"><div class="stat-card"><div class="stat-icon">✅</div><div><span>الجلسة</span><strong>نشطة</strong></div></div></div>
    </div>

    <div class="card card-body">
        <h2 class="h5 mb-2">تم تسجيل الدخول بنجاح</h2>
        <p class="text-muted mb-0">استخدم القائمة الجانبية للوصول إلى الصفحات المتاحة حسب صلاحياتك.</p>
    </div>
</x-app-layout>
