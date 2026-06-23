@csrf
@php($mode = $mode ?? 'create')
<div class="tax-form" dir="rtl">
    <div class="tax-form-head">
        <h2 class="h4 mb-1">{{ $mode === 'edit' ? 'تعديل إعداد الضريبة' : 'إعداد ضريبة جديد' }}</h2>
        <p class="mb-0 opacity-75">اضبط نوع الضريبة ونسبتها وكود جوفوتارا المستخدم عند إعداد الفواتير.</p>
    </div>
    <div class="tax-form-body">
        <div class="alert alert-danger validation-summary" data-validation-summary></div>
        <section class="tax-section">
            <div class="tax-section-title">1. البيانات الأساسية</div>
            <div class="row g-3">
                <div class="col-md-5"><label class="form-label">اسم الضريبة</label><input name="name" class="form-control" placeholder="مثال: ضريبة مبيعات 16%" value="{{ old('name', $taxProfile->name) }}" required>
                    <div class="form-text">اسم داخلي واضح يظهر عند اختيار الضريبة.</div>@error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-5"><label class="form-label">نوع الضريبة</label><select name="tax_type" class="form-select" required>
                        <option value="">اختر نوع الضريبة</option>@foreach(['sales' => 'ضريبة مبيعات', 'zero' => 'ضريبة صفرية', 'exempt' => 'معفاة', 'special' => 'ضريبة خاصة'] as $value => $label)<option value="{{ $value }}" @selected(old('tax_type', $taxProfile->tax_type)===$value)>{{ $label }}</option>@endforeach
                    </select>
                    <div class="form-text">نوع الضريبة يحدد طريقة تصنيفها داخلياً.</div>@error('tax_type')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-5"><label class="form-label">نسبة الضريبة</label><input name="tax_percent" type="number" min="0" max="100" step="0.000001" class="form-control" placeholder="مثال: 16" value="{{ old('tax_percent', $taxProfile->tax_percent) }}" required>
                    <div class="form-text">النسبة الرقمية التي ستُستخدم في حساب إجمالي الضريبة.</div>@error('tax_percent')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-5"><label class="form-label">كود جوفوتارا</label><input name="jofotara_tax_code" class="form-control" placeholder="مثال: S أو Z عند الحاجة" value="{{ old('jofotara_tax_code', $taxProfile->jofotara_tax_code) }}">
                    <div class="form-text">كود التصنيف الضريبي الرسمي عند الحاجة، وليس نسبة الضريبة.</div>@error('jofotara_tax_code')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>
        </section>
        <section class="tax-section">
            <div class="tax-section-title">2. الحالة والاستخدام</div>
            <div class="row g-3">
                <div class="col-md-5"><label class="form-label">الحالة</label>
                    <div class="status-card"><label class="form-check mb-0"><input name="is_default" value="1" type="checkbox" class="form-check-input" @checked(old('is_default', $taxProfile->is_default))> جعلها الضريبة الافتراضية</label></div>
                </div>
                <div class="col-md-5">
                    <div class="status-card"><label class="form-check mb-0"><input name="is_active" value="1" type="checkbox" class="form-check-input" @checked(old('is_active', $taxProfile->is_active ?? true))> نشطة ومتاحة للاستخدام</label></div>
                </div>
            </div>
        </section>
        <div class="hint-card">الفرق باختصار: <strong>نسبة الضريبة</strong> هي الرقم المستخدم بالحساب، <strong>نوع الضريبة</strong> وصف إداري للتصنيف， و<strong>كود جوفوتارا</strong> هو الرمز الرسمي عند الربط الضريبي.</div>
    </div>
    <div class="tax-actions d-flex flex-wrap gap-2"><button class="btn btn-primary" name="save_action" value="save">{{ $mode === 'edit' ? 'حفظ التعديلات' : 'حفظ' }}</button>@if($mode !== 'edit')<button class="btn btn-outline-primary" name="save_action" value="save_another">حفظ وإضافة أخرى</button>@endif<a class="btn btn-outline-secondary" href="{{ route('company.tax-profiles.index', ['company' => $routeCompanyId]) }}">إلغاء</a></div>
</div>
