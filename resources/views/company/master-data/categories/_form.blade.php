@csrf
@php($mode = $mode ?? 'create')
@php($icons = ['🗂️','🍽️','🛒','🧾','📦','🧰','💻','🚚','🏷️','⭐','☕','🔧'])
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
