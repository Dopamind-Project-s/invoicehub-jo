@csrf

<div class="row g-4" dir="rtl">

    {{-- معلومات المنشأة --}}
    <div class="col-12">
        <h5 class="border-bottom pb-2 mb-3">معلومات المنشأة</h5>
    </div>

    <div class="col-md-5">
        <label class="form-label">اسم المنشأة عربي</label>
        <input name="name_ar" class="form-control"
               value="{{ old('name_ar', $company->name_ar ?: $company->legal_name_ar) }}" required>
    </div>

    <div class="col-md-5">
        <label class="form-label">اسم المنشأة إنجليزي</label>
        <input name="name_en" class="form-control"
               value="{{ old('name_en', $company->name_en ?: $company->legal_name_en) }}">
    </div>

    <div class="col-md-2">
        <label class="form-label">العملة</label>
        <input name="default_currency" class="form-control text-uppercase" maxlength="3"
               value="{{ old('default_currency', $company->default_currency ?: 'JOD') }}" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">شعار المنشأة</label>
        <input name="logo" type="file" accept="image/*" class="form-control">

        @if($company->logo_path)
            <img src="{{ asset('storage/'.$company->logo_path) }}"
                 alt="شعار المنشأة"
                 class="mt-2 rounded border bg-white p-1"
                 style="max-height:64px">
        @endif
    </div>

    {{-- بيانات الضريبة والفوترة --}}
    <div class="col-12">
        <h5 class="border-bottom pb-2 mb-3 mt-2">بيانات الضريبة والفوترة</h5>
    </div>

    <div class="col-md-2">
        <label class="form-label">الرقم الضريبي</label>
        <input name="tax_number" class="form-control"
               value="{{ old('tax_number', $company->tax_number) }}" required>
    </div>

    <div class="col-md-2">
        <label class="form-label">تسلسل مصدر الدخل</label>
        <input name="jofotara_source_id" class="form-control"
               value="{{ old('jofotara_source_id', $company->jofotara_source_id) }}">
    </div>

    <div class="col-md-2">
        <label class="form-label">الرقم الوطني</label>
        <input name="national_number" class="form-control"
               value="{{ old('national_number', $company->national_number) }}">
    </div>

    <div class="col-md-2">
        <label class="form-label">Client ID</label>
        <input name="jofotara_client_id" type="password" autocomplete="new-password"
               class="form-control"
               placeholder="{{ $company->exists && $company->hasJofotaraClientId() ? 'محفوظ ومشفّر — اتركه فارغاً للإبقاء عليه' : 'أدخل Client ID' }}">
    </div>

    <div class="col-md-2">
        <label class="form-label">Secret Key</label>
        <input name="jofotara_secret_key" type="password" autocomplete="new-password"
               class="form-control"
               placeholder="{{ $company->exists && $company->hasJofotaraSecretKey() ? 'محفوظ ومشفّر — اتركه فارغاً للإبقاء عليه' : 'أدخل Secret Key' }}">
    </div>

    {{-- بيانات الاتصال --}}
    <div class="col-12">
        <h5 class="border-bottom pb-2 mb-3 mt-2">بيانات الاتصال</h5>
    </div>

    <div class="col-md-4">
        <label class="form-label">الهاتف</label>
        <input name="phone" class="form-control"
               value="{{ old('phone', $company->phone) }}">
    </div>

    <div class="col-md-4">
        <label class="form-label">البريد الإلكتروني</label>
        <input name="email" type="email" class="form-control"
               value="{{ old('email', $company->email) }}">
    </div>

    {{-- إعدادات النظام --}}
    <div class="col-12">
        <h5 class="border-bottom pb-2 mb-3 mt-2">إعدادات النظام</h5>
    </div>

    <div class="col-md-3">
        <label class="form-label">الحالة</label>
        <select name="status" class="form-select">
            <option value="active" @selected(old('status', $company->status ?: 'active') === 'active')>
                نشطة
            </option>
            <option value="suspended" @selected(old('status', $company->status) === 'suspended')>
                معلقة
            </option>
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">اللغة الافتراضية</label>
        <select name="default_language" class="form-select">
            <option value="ar" @selected(old('default_language', $company->default_language ?: 'ar') === 'ar')>
                العربية
            </option>
            <option value="en" @selected(old('default_language', $company->default_language) === 'en')>
                English
            </option>
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">الباقة</label>
        <select name="plan_id" class="form-select">
            <option value="">بدون باقة محددة</option>

            @foreach($plans as $planOption)
                <option value="{{ $planOption->id }}"
                    @selected((int) old('plan_id', $selectedPlanId ?? 0) === $planOption->id)>
                    {{ $planOption->name }} — {{ number_format((float) $planOption->monthly_price, 3) }} شهرياً
                </option>
            @endforeach
        </select>

        <div class="form-text">
            اختيار الباقة يضيف مفاتيحها تلقائياً، ويمكن إبقاء مفاتيح يدوية كاستثناءات.
        </div>
    </div>

    {{-- مفاتيح المزايا --}}
    <div class="col-12">
        <h5 class="border-bottom pb-2 mb-3 mt-3">مفاتيح المزايا</h5>
    </div>

    @foreach($features as $feature)
        <div class="col-md-4">
            <label class="form-check border rounded p-3 h-100">
                <input class="form-check-input"
                       type="checkbox"
                       name="feature_keys[]"
                       value="{{ $feature->id }}"
                       @checked(in_array($feature->id, old('feature_keys', $enabledFeatureIds ?? [])))>

                <span class="form-check-label d-block pe-2">
                    <strong>{{ $feature->name_ar ?: $feature->name }}</strong>
                    <br>
                    <small class="text-muted">{{ $feature->code }} — {{ $feature->category }}</small>

                    @if($feature->description)
                        <br>
                        <small>{{ $feature->description }}</small>
                    @endif
                </span>
            </label>
        </div>
    @endforeach

    <div class="col-12">
        <button type="submit" class="btn btn-primary mt-3">
            حفظ
        </button>
    </div>

</div>