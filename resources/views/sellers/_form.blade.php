@csrf
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">اسم البائع</label>
        <input name="name" class="form-control" value="{{ old('name', $seller->name) }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">الرقم الضريبي</label>
        <input name="tax_number" class="form-control" value="{{ old('tax_number', $seller->tax_number) }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">الرقم الوطني</label>
        <input name="national_number" class="form-control" value="{{ old('national_number', $seller->national_number) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">تسلسل مصدر الدخل</label>
        <input name="income_source_sequence" class="form-control" value="{{ old('income_source_sequence', $seller->income_source_sequence) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">الهاتف</label>
        <input name="phone" class="form-control" value="{{ old('phone', $seller->phone) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">البريد الإلكتروني</label>
        <input name="email" type="email" class="form-control" value="{{ old('email', $seller->email) }}">
    </div>
    <div class="col-md-12">
        <label class="form-label">العنوان</label>
        <textarea name="address" class="form-control">{{ old('address', $seller->address) }}</textarea>
    </div>
    <div class="col-md-4">
        <label class="form-label">الشعار</label>
        <input name="logo" type="file" accept="image/png,image/jpeg,image/webp" class="form-control">
        @if($seller->logo_path)
            <img src="{{ asset('storage/'.$seller->logo_path) }}" class="img-thumbnail mt-2" style="max-width: 120px" alt="شعار البائع">
        @endif
    </div>
    <div class="col-md-4">
        <label class="form-label">JoFotara Client ID</label>
        <input name="jofotara_client_id" class="form-control" value="{{ old('jofotara_client_id', $seller->jofotara_client_id) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">JoFotara Secret Key</label>
        <input name="jofotara_secret_key" type="password" class="form-control" autocomplete="new-password">
    </div>
    <div class="col-md-4">
        <label class="form-label">JoFotara Source ID</label>
        <input name="jofotara_source_id" class="form-control" value="{{ old('jofotara_source_id', $seller->jofotara_source_id) }}">
    </div>
    <div class="col-md-12 form-check mt-4">
        <input type="hidden" name="is_default" value="0">
        <input name="is_default" value="1" class="form-check-input" type="checkbox" id="is_default" @checked(old('is_default', $seller->is_default))>
        <label class="form-check-label" for="is_default">البائع الافتراضي</label>
    </div>
</div>
<button class="btn btn-primary mt-3">حفظ</button>
