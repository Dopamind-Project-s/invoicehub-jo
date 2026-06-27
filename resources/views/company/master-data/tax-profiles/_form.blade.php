@csrf
@php($mode = $mode ?? 'create')
<style>
    .tax-form {
        border: 1px solid #e5eef4;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 16px 36px rgba(15, 23, 42, .07);
        background: #fff
    }

    .tax-form-head {
        background: linear-gradient(135deg, #00a9c4, #12c2b2);
        color: #fff;
        padding: 22px 24px
    }

    .tax-form-body {
        padding: 24px
    }

    .tax-section {
        border: 1px solid #e8f0f5;
        border-radius: 18px;
        padding: 18px;
        background: #fff;
        margin-bottom: 18px
    }

    .tax-section-title {
        font-weight: 800;
        margin-bottom: 14px
    }

    .tax-form .form-control,
    .tax-form .form-select {
        border-radius: 14px
    }

    .form-text {
        font-size: .8rem
    }

    .tax-actions {
        background: #f8fbfc;
        border-top: 1px solid #e5eef4;
        padding: 18px 24px
    }

    .tax-actions .btn {
        border-radius: 999px;
        min-width: 140px
    }

    .validation-summary {
        display: none;
        border-radius: 14px
    }

    .status-card {
        border-radius: 16px;
        background: #f8fdff;
        border: 1px solid #d7eef3;
        padding: 14px
    }

    .hint-card {
        border-radius: 16px;
        background: #fff8e6;
        border: 1px solid #ffe2a8;
        color: #7a5200;
        padding: 14px
    }
</style>
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[data-tax-form]').forEach(function(form) {
            var summary = form.querySelector('[data-validation-summary]');

            function showErrors(errors) {
                summary.style.display = errors.length ? 'block' : 'none';
                summary.innerHTML = errors.map(function(e) {
                    return '<div>' + e + '</div>';
                }).join('');
            }
            form.addEventListener('submit', function(event) {
                var errors = [];
                var name = form.querySelector('[name="name"]');
                var type = form.querySelector('[name="tax_type"]');
                var percent = form.querySelector('[name="tax_percent"]');
                if (!name.value.trim()) errors.push('اسم الضريبة مطلوب ولا يمكن تركه فارغاً.');
                if (!type.value.trim()) errors.push('نوع الضريبة مطلوب.');
                if (percent.value === '' || Number(percent.value) < 0 || Number(percent.value) > 100) errors.push('نسبة الضريبة مطلوبة ويجب أن تكون بين 0 و100.');
                if (errors.length) {
                    event.preventDefault();
                    showErrors(errors);
                    window.scrollTo({
                        top: form.offsetTop - 20,
                        behavior: 'smooth'
                    });
                }
            });
        });
    });
</script>