<div class="row g-3" dir="rtl">
    <div class="col-md-6"><label class="form-label">الاسم القانوني عربي</label><input name="legal_name_ar" class="form-control" value="{{ old('legal_name_ar', $company->legal_name_ar) }}" required></div>
    <div class="col-md-6"><label class="form-label">الاسم القانوني إنجليزي</label><input name="legal_name_en" class="form-control" value="{{ old('legal_name_en', $company->legal_name_en) }}"></div>
    <div class="col-md-6"><label class="form-label">الاسم التجاري</label><input name="trade_name" class="form-control" value="{{ old('trade_name', $company->trade_name) }}"></div>
    <div class="col-md-6"><label class="form-label">الرقم الضريبي</label><input name="tax_number" class="form-control" value="{{ old('tax_number', $company->tax_number) }}" required></div>
    <div class="col-md-4"><label class="form-label">تسلسل مصدر الدخل</label><input name="jofotara_source_id" class="form-control" value="{{ old('jofotara_source_id', $company->jofotara_source_id) }}"></div>
    <div class="col-md-4"><label class="form-label">Client ID</label><input name="jofotara_client_id" type="password" autocomplete="new-password" class="form-control" placeholder="{{ $company->exists && $company->hasJofotaraClientId() ? 'محفوظ ومشفّر — اتركه فارغاً للإبقاء عليه' : 'أدخل Client ID' }}"></div>
    <div class="col-md-4"><label class="form-label">Secret Key</label><input name="jofotara_secret_key" type="password" autocomplete="new-password" class="form-control" placeholder="{{ $company->exists && $company->hasJofotaraSecretKey() ? 'محفوظ ومشفّر — اتركه فارغاً للإبقاء عليه' : 'أدخل Secret Key' }}"></div>
    <div class="col-md-4"><label class="form-label">الهاتف</label><input name="phone" class="form-control" value="{{ old('phone', $company->phone) }}"></div>
    <div class="col-md-2"><label class="form-label">الدولة</label><input name="country_code" class="form-control" value="{{ old('country_code', $company->country_code ?: 'JO') }}" required maxlength="2"></div>
    <div class="col-md-3"><label class="form-label">المدينة</label><input name="city" class="form-control" value="{{ old('city', $company->city) }}"></div>
    <div class="col-md-3"><label class="form-label">الشارع</label><input name="street" class="form-control" value="{{ old('street', $company->street) }}"></div>
    <div class="col-md-3"><label class="form-label">العملة الافتراضية</label><input name="default_currency" class="form-control" value="{{ old('default_currency', $company->default_currency ?: 'JOD') }}" required maxlength="3"></div>
    <div class="col-md-3"><label class="form-label">بادئة ICV</label><input name="icv_prefix" class="form-control" value="{{ old('icv_prefix', $company->icv_prefix ?: 'INV') }}" required></div>
    <div class="col-md-3 d-flex align-items-end"><div class="form-check"><input type="hidden" name="is_active" value="0"><input name="is_active" value="1" class="form-check-input" type="checkbox" @checked(old('is_active', $company->is_active))><label class="form-check-label">نشطة</label></div></div>
</div>
<button class="btn btn-primary mt-4">حفظ</button>
