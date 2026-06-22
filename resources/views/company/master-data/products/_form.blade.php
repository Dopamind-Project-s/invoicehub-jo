@csrf
@php($mode = $mode ?? 'create')
<style>
.product-form{border:1px solid #e5eef4;border-radius:24px;overflow:hidden;box-shadow:0 16px 36px rgba(15,23,42,.07);background:#fff}.product-form-head{background:linear-gradient(135deg,#00a9c4,#12c2b2);color:#fff;padding:22px 24px}.product-form-body{padding:24px}.product-section{border:1px solid #e8f0f5;border-radius:18px;padding:18px;background:#fff;margin-bottom:18px}.product-section-title{font-weight:800;margin-bottom:14px}.product-form .form-control,.product-form .form-select{border-radius:14px}.product-form .form-text{font-size:.8rem}.image-preview-box{border:1px dashed #b7dce5;border-radius:18px;background:#f8fdff;padding:16px;text-align:center}.product-image-preview{width:150px;height:150px;border-radius:22px;object-fit:cover;background:#eef6f8}.product-actions{background:#f8fbfc;border-top:1px solid #e5eef4;padding:18px 24px}.product-actions .btn{border-radius:999px;min-width:150px}.validation-summary{display:none;border-radius:14px}.status-card{border-radius:16px;background:#f8fdff;border:1px solid #d7eef3;padding:14px}
</style>
<div class="product-form" dir="rtl">
    <div class="product-form-head"><h2 class="h4 mb-1">{{ $mode === 'edit' ? 'تعديل بيانات المنتج / الخدمة' : 'منتج / خدمة جديدة' }}</h2><p class="mb-0 opacity-75">نظّم بيانات الكتالوج لتسهيل إنشاء الفواتير بسرعة ودقة.</p></div>
    <div class="product-form-body">
        <div class="alert alert-danger validation-summary" data-validation-summary></div>
        <section class="product-section"><div class="product-section-title">1. المعلومات الأساسية</div><div class="row g-3">
            <div class="col-md-3"><label class="form-label">النوع</label><select name="type" class="form-select" required><option value="">اختر النوع</option><option value="product" @selected(old('type', $product->type)==='product')>منتج</option><option value="service" @selected(old('type', $product->type)==='service')>خدمة</option></select></div>
            <div class="col-md-3"><label class="form-label">SKU</label><input name="sku" class="form-control" placeholder="مثال: PRD-001" value="{{ old('sku', $product->sku) }}" required></div>
            <div class="col-md-3"><label class="form-label">الباركود</label><input name="barcode" class="form-control" placeholder="اختياري" value="{{ old('barcode', $product->barcode) }}"></div>
            <div class="col-md-3"><label class="form-label">الحالة</label><div class="status-card"><label class="form-check mb-0"><input name="is_active" value="1" type="checkbox" class="form-check-input" @checked(old('is_active', $product->is_active ?? true))> نشط ومتاح للفواتير</label></div></div>
            <div class="col-md-6"><label class="form-label">الاسم العربي</label><input name="name_ar" class="form-control" placeholder="مثال: وجبة منسف" value="{{ old('name_ar', $product->name_ar) }}" required></div>
            <div class="col-md-6"><label class="form-label">الاسم الإنجليزي</label><input name="name_en" class="form-control" placeholder="مثال: Mansaf meal" value="{{ old('name_en', $product->name_en) }}"></div>
        </div></section>
        <section class="product-section"><div class="product-section-title">2. التصنيف والوحدة والضريبة</div><div class="row g-3">
            <div class="col-md-4"><label class="form-label">التصنيف</label><select name="category_id" class="form-select"><option value="">بدون تصنيف</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id)===(string) $category->id)>{{ $category->name_ar }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label">الوحدة</label><select name="unit_id" class="form-select" required><option value="">اختر الوحدة</option>@foreach($units as $unit)<option value="{{ $unit->id }}" @selected((string) old('unit_id', $product->unit_id)===(string) $unit->id)>{{ $unit->name_ar }} ({{ $unit->code }})</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label">ملف الضريبة</label><select name="tax_profile_id" class="form-select"><option value="">بدون ملف ضريبي</option>@foreach($taxProfiles as $taxProfile)<option value="{{ $taxProfile->id }}" @selected((string) old('tax_profile_id', $product->tax_profile_id)===(string) $taxProfile->id)>{{ $taxProfile->name }}</option>@endforeach</select></div>
        </div></section>
        <section class="product-section"><div class="product-section-title">3. التسعير</div><div class="row g-3">
            <div class="col-md-4"><label class="form-label">السعر</label><input name="price" type="number" min="0" step="0.000001" class="form-control" placeholder="0.000000" value="{{ old('price', $product->price) }}" required><div class="form-text">السعر الذي سيظهر افتراضياً في الفاتورة.</div></div>
            <div class="col-md-4"><label class="form-label">التكلفة</label><input name="cost" type="number" min="0" step="0.000001" class="form-control" placeholder="اختياري" value="{{ old('cost', $product->cost) }}"></div>
            <div class="col-md-4"><label class="form-label">العملة</label><input class="form-control" value="{{ $company->default_currency ?: 'JOD' }}" disabled><div class="form-text">يتم ضبط العملة من إعدادات المنشأة.</div></div>
        </div></section>
        <section class="product-section"><div class="product-section-title">4. الوصف والصورة</div><div class="row g-3">
            <div class="col-md-7"><label class="form-label">الوصف</label><textarea name="description" class="form-control" rows="7" placeholder="اكتب وصفاً مختصراً يظهر لفريق العمل أو على الفاتورة عند الحاجة">{{ old('description', $product->description) }}</textarea></div>
            <div class="col-md-5"><label class="form-label">صورة المنتج</label><div class="image-preview-box"><img data-image-preview class="product-image-preview mb-3" src="{{ $product->image_path ? asset('storage/'.$product->image_path) : asset('assets/images/invoice-placeholder-logo.svg') }}" alt="معاينة الصورة"><input name="image" type="file" class="form-control" accept="image/jpeg,image/png,image/webp" data-image-input><div class="form-text mt-2">JPG أو PNG أو WEBP — الحد الأقصى 2MB.</div></div></div>
        </div></section>
    </div>
    <div class="product-actions d-flex flex-wrap gap-2">
        <button class="btn btn-primary" name="save_action" value="save">{{ $mode === 'edit' ? 'حفظ التعديلات' : 'حفظ المنتج' }}</button>
        @if($mode !== 'edit')<button class="btn btn-outline-primary" name="save_action" value="save_another">حفظ وإضافة منتج آخر</button>@endif
        <a class="btn btn-outline-secondary" href="{{ route('company.products.index', ['company' => $routeCompanyId]) }}">إلغاء</a>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-product-form]').forEach(function (form) {
        var imageInput = form.querySelector('[data-image-input]');
        var imagePreview = form.querySelector('[data-image-preview]');
        var summary = form.querySelector('[data-validation-summary]');
        function showErrors(errors) { summary.style.display = errors.length ? 'block' : 'none'; summary.innerHTML = errors.map(function (e) { return '<div>'+e+'</div>'; }).join(''); }
        if (imageInput && imagePreview) {
            imageInput.addEventListener('change', function () {
                var file = imageInput.files[0];
                if (!file) return;
                if (!['image/jpeg','image/png','image/webp'].includes(file.type) || file.size > 2 * 1024 * 1024) { showErrors(['صيغة الصورة أو حجمها غير صحيح.']); imageInput.value = ''; return; }
                imagePreview.src = URL.createObjectURL(file);
                showErrors([]);
            });
        }
        form.addEventListener('submit', function (event) {
            var errors = [];
            var nameAr = form.querySelector('[name="name_ar"]');
            var type = form.querySelector('[name="type"]');
            var price = form.querySelector('[name="price"]');
            var cost = form.querySelector('[name="cost"]');
            if (!nameAr.value.trim()) errors.push('الاسم العربي مطلوب.');
            if (!type.value) errors.push('نوع المنتج مطلوب.');
            if (price.value === '' || Number(price.value) < 0) errors.push('السعر مطلوب ويجب أن يكون 0 أو أكثر.');
            if (cost.value !== '' && Number(cost.value) < 0) errors.push('التكلفة يجب أن تكون 0 أو أكثر.');
            if (imageInput && imageInput.files[0]) {
                var file = imageInput.files[0];
                if (!['image/jpeg','image/png','image/webp'].includes(file.type)) errors.push('صيغة الصورة يجب أن تكون JPG أو PNG أو WEBP.');
                if (file.size > 2 * 1024 * 1024) errors.push('حجم الصورة يجب ألا يتجاوز 2MB.');
            }
            if (errors.length) { event.preventDefault(); showErrors(errors); window.scrollTo({ top: form.offsetTop - 20, behavior: 'smooth' }); }
        });
    });
});
</script>
