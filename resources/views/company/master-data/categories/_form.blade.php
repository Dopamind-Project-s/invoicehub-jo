@csrf
@php($mode = $mode ?? 'create')
@php($icons = ['🗂️','🍽️','🛒','🧾','📦','🧰','💻','🚚','🏷️','⭐','☕','🔧'])
<style>
.category-form{border:1px solid #e5eef4;border-radius:24px;overflow:hidden;box-shadow:0 16px 36px rgba(15,23,42,.07);background:#fff}.category-form-head{background:linear-gradient(135deg,#00a9c4,#12c2b2);color:#fff;padding:22px 24px}.category-form-body{padding:24px}.category-section{border:1px solid #e8f0f5;border-radius:18px;padding:18px;background:#fff;margin-bottom:18px}.category-section-title{font-weight:800;margin-bottom:14px}.category-form .form-control,.category-form .form-select{border-radius:14px}.category-form .form-text{font-size:.8rem}.category-icon-picker{display:grid;grid-template-columns:repeat(auto-fit,minmax(52px,1fr));gap:10px}.category-icon-option{position:relative}.category-icon-option input{position:absolute;opacity:0;pointer-events:none}.category-icon-tile{display:grid;place-items:center;height:48px;border:1px solid #d7eef3;border-radius:16px;background:#f8fdff;font-size:1.35rem;cursor:pointer;transition:.15s}.category-icon-option input:checked+.category-icon-tile{background:#00a9c4;color:#fff;border-color:#00a9c4;box-shadow:0 10px 24px rgba(0,169,196,.22)}.category-actions{background:#f8fbfc;border-top:1px solid #e5eef4;padding:18px 24px}.category-actions .btn{border-radius:999px;min-width:140px}.validation-summary{display:none;border-radius:14px}.status-card{border-radius:16px;background:#f8fdff;border:1px solid #d7eef3;padding:14px}.current-icon{width:62px;height:62px;border-radius:20px;background:#eefcff;border:1px solid #d7eef3;display:grid;place-items:center;font-size:1.8rem}
</style>
<div class="category-form" dir="rtl">
    <div class="category-form-head"><h2 class="h4 mb-1">{{ $mode === 'edit' ? 'تعديل بيانات الفئة' : 'فئة منتجات جديدة' }}</h2><p class="mb-0 opacity-75">رتّب منتجاتك وخدماتك داخل مجموعات واضحة وسهلة البحث.</p></div>
    <div class="category-form-body">
        <div class="alert alert-danger validation-summary" data-validation-summary></div>
        <section class="category-section"><div class="category-section-title">1. البيانات الأساسية</div><div class="row g-3">
            <div class="col-md-5"><label class="form-label">الاسم العربي</label><input name="name_ar" class="form-control" placeholder="مثال: الوجبات الرئيسية" value="{{ old('name_ar', $category->name_ar) }}" required><div class="form-text">سيظهر هذا الاسم في صفحات إدارة المنتجات.</div>@error('name_ar')<div class="text-danger small">{{ $message }}</div>@enderror</div>
            <div class="col-md-5"><label class="form-label">الاسم الإنجليزي</label><input name="name_en" class="form-control" placeholder="Example: Main meals" value="{{ old('name_en', $category->name_en) }}">@error('name_en')<div class="text-danger small">{{ $message }}</div>@enderror</div>
            <div class="col-md-5"><label class="form-label">الكود</label><input name="code" class="form-control" placeholder="مثال: MAIN-MEALS" value="{{ old('code', $category->code) }}" required><div class="form-text">كود داخلي مختصر وفريد داخل المنشأة.</div>@error('code')<div class="text-danger small">{{ $message }}</div>@enderror</div>
            <div class="col-md-5"><label class="form-label">الحالة</label><div class="status-card"><label class="form-check mb-0"><input name="is_active" value="1" type="checkbox" class="form-check-input" @checked(old('is_active', $category->is_active ?? true))> نشطة ومتاحة للمنتجات</label></div>@error('is_active')<div class="text-danger small">{{ $message }}</div>@enderror</div>
        </div></section>
        <section class="category-section"><div class="d-flex align-items-center gap-3 mb-3"><div class="current-icon" data-current-icon>{{ old('icon', $category->icon ?: '🗂️') }}</div><div><div class="category-section-title mb-1">2. أيقونة الفئة</div><div class="text-muted small">اختر أيقونة بسيطة تساعد فريقك على تمييز الفئات بسرعة.</div></div></div><div class="category-icon-picker">
            @foreach($icons as $icon)<label class="category-icon-option"><input type="radio" name="icon" value="{{ $icon }}" @checked(old('icon', $category->icon ?: '🗂️') === $icon) data-icon-choice><span class="category-icon-tile">{{ $icon }}</span></label>@endforeach
        </div>@error('icon')<div class="text-danger small mt-2">{{ $message }}</div>@enderror</section>
        <section class="category-section"><div class="category-section-title">3. الوصف</div><label class="form-label">وصف الفئة</label><textarea name="description" class="form-control" rows="5" placeholder="اكتب وصفاً مختصراً يوضح المنتجات أو الخدمات التي تنتمي لهذه الفئة">{{ old('description', $category->description) }}</textarea><div class="form-text">اختياري، لكنه يساعد في توحيد طريقة التصنيف داخل المنشأة.</div>@error('description')<div class="text-danger small">{{ $message }}</div>@enderror</section>
    </div>
    <div class="category-actions d-flex flex-wrap gap-2"><button class="btn btn-primary" name="save_action" value="save">{{ $mode === 'edit' ? 'حفظ التعديلات' : 'حفظ' }}</button>@if($mode !== 'edit')<button class="btn btn-outline-primary" name="save_action" value="save_another">حفظ وإضافة أخرى</button>@endif<a class="btn btn-outline-secondary" href="{{ route('company.product-categories.index', ['company' => $routeCompanyId]) }}">إلغاء</a></div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-category-form]').forEach(function (form) {
        var summary = form.querySelector('[data-validation-summary]');
        var currentIcon = form.querySelector('[data-current-icon]');
        function showErrors(errors) { summary.style.display = errors.length ? 'block' : 'none'; summary.innerHTML = errors.map(function (e) { return '<div>'+e+'</div>'; }).join(''); }
        form.querySelectorAll('[data-icon-choice]').forEach(function (choice) { choice.addEventListener('change', function () { if (currentIcon) currentIcon.textContent = choice.value; }); });
        form.addEventListener('submit', function (event) {
            var errors = [];
            var nameAr = form.querySelector('[name="name_ar"]');
            var code = form.querySelector('[name="code"]');
            if (!nameAr.value.trim()) errors.push('الاسم العربي مطلوب ولا يمكن تركه فارغاً.');
            if (!code.value.trim()) errors.push('كود الفئة مطلوب ولا يمكن تركه فارغاً.');
            if (errors.length) { event.preventDefault(); showErrors(errors); window.scrollTo({ top: form.offsetTop - 20, behavior: 'smooth' }); }
        });
    });
});
</script>
