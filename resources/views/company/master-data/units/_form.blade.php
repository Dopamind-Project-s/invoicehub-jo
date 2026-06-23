@csrf
@php($mode = $mode ?? 'create')
<div class="master-form" dir="rtl">
    <div class="master-form-head">
        <h2 class="h4 mb-1">{{ $mode === 'edit' ? 'تعديل بيانات الوحدة' : 'وحدة قياس جديدة' }}</h2>
        <p class="mb-0 opacity-75">استخدم وحدة واضحة لتوحيد قياس المنتجات والخدمات في الفواتير.</p>
    </div>
    <div class="master-form-body">
        <div class="alert alert-danger validation-summary" data-validation-summary></div>
        <section class="master-section">
            <div class="master-section-title">1. بيانات الوحدة</div>
            <div class="row g-3">
                <div class="col-md-5"><label class="form-label">الاسم العربي</label><input name="name_ar" class="form-control" placeholder="مثال: قطعة" value="{{ old('name_ar', $unit->name_ar) }}" required>
                    <div class="form-text">اسم الوحدة الذي يظهر لفريق العمل.</div>@error('name_ar')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-5"><label class="form-label">الاسم الإنجليزي</label><input name="name_en" class="form-control" placeholder="Example: Piece" value="{{ old('name_en', $unit->name_en) }}">@error('name_en')<div class="text-danger small">{{ $message }}</div>@enderror</div>
                <div class="col-md-3"><label class="form-label">الكود</label><input name="code" class="form-control" placeholder="مثال: PCS" value="{{ old('code', $unit->code) }}" required>
                    <div class="form-text">كود مختصر وفريد داخل المنشأة.</div>@error('code')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3"><label class="form-label">الرمز</label><input name="symbol" class="form-control" placeholder="مثال: pc" value="{{ old('symbol', $unit->symbol) }}">@error('symbol')<div class="text-danger small">{{ $message }}</div>@enderror</div>
                
            </div>
        </section>
        <section class="master-section">
            <div class="master-section-title">2. الوصف</div><textarea name="description" class="form-control" rows="5" placeholder="اكتب ملاحظة داخلية توضح استخدام هذه الوحدة">{{ old('description', $unit->description) }}</textarea>
            <div class="form-text">اختياري، مثل: تستخدم للمنتجات الفردية أو الخدمات حسب الساعة.</div>@error('description')<div class="text-danger small">{{ $message }}</div>@enderror
        </section>
        <section class="master-section">
           <div class="col-md-3"><label class="form-label">الحالة</label>
                    <div class="status-card"><label class="form-check mb-0"><input name="is_active" value="1" type="checkbox" class="form-check-input" @checked(old('is_active', $unit->is_active ?? true))> نشطة ومتاحة للمنتجات</label></div>
                </div>
        </section>
    </div>
    <div class="master-actions d-flex flex-wrap gap-2"><button class="btn btn-primary" name="save_action" value="save">{{ $mode === 'edit' ? 'حفظ التعديلات' : 'حفظ' }}</button>@if($mode !== 'edit')<button class="btn btn-outline-primary" name="save_action" value="save_another">حفظ وإضافة أخرى</button>@endif<a class="btn btn-outline-secondary" href="{{ route('company.units.index', ['company' => $routeCompanyId]) }}">إلغاء</a></div>
</div>
