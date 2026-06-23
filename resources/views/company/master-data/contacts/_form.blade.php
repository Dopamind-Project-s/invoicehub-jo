@csrf
@php($mode = $mode ?? 'create')
<div class="contact-form" dir="rtl">
    <div class="contact-form-head">
        <h2 class="h4 mb-1">{{ $mode === 'edit' ? 'تعديل بيانات جهة الاتصال' : 'عميل / مورد جديد' }}</h2>
        <p class="mb-0 opacity-75">نظّم بيانات العملاء والموردين لتسريع إنشاء الفواتير وتقليل الأخطاء.</p>
    </div>
    <div class="contact-form-body">
        <div class="alert alert-danger validation-summary" data-validation-summary></div>
        <section class="contact-section">
            <div class="contact-section-title">1. المعلومات الأساسية</div>
            <div class="row g-3">
                <div class="col-md-3"><label class="form-label">النوع</label><select name="type" class="form-select" required>
                        <option value="">اختر النوع</option>
                        <option value="customer" @selected(old('type', $contact->type)==='customer')>عميل</option>
                        <option value="supplier" @selected(old('type', $contact->type)==='supplier')>مورد</option>
                        <option value="both" @selected(old('type', $contact->type)==='both')>عميل ومورد</option>
                    </select>@error('type')<div class="text-danger small">{{ $message }}</div>@enderror</div>
                <div class="col-md-3"><label class="form-label">الاسم العربي</label><input name="name_ar" class="form-control" placeholder="مثال: شركة النور" value="{{ old('name_ar', $contact->name_ar) }}" required>@error('name_ar')<div class="text-danger small">{{ $message }}</div>@enderror</div>
                <div class="col-md-3"><label class="form-label">الاسم الإنجليزي</label><input name="name_en" class="form-control" placeholder="Example: Al Noor Co." value="{{ old('name_en', $contact->name_en) }}">@error('name_en')<div class="text-danger small">{{ $message }}</div>@enderror</div>
            </div>
        </section>
        <section class="contact-section">
            <div class="contact-section-title">2. بيانات التواصل</div>
            <div class="row g-3">
                <div class="col-md-5"><label class="form-label">الهاتف</label><input name="phone" class="form-control" placeholder="مثال: 0790000000" value="{{ old('phone', $contact->phone) }}">
                    <div class="form-text">يفضل استخدام أرقام فقط مع السماح بعلامة + عند الحاجة.</div>@error('phone')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-5"><label class="form-label">البريد الإلكتروني</label><input name="email" type="email" class="form-control" placeholder="name@example.com" value="{{ old('email', $contact->email) }}">@error('email')<div class="text-danger small">{{ $message }}</div>@enderror</div>
            </div>
        </section>
        <section class="contact-section">
            <div class="contact-section-title">3. البيانات الضريبية</div>
            <div class="row g-3">
                <div class="col-md-5"><label class="form-label">الرقم الضريبي</label><input name="tax_number" class="form-control" placeholder="مثال: 123456789" value="{{ old('tax_number', $contact->tax_number) }}">
                    <div class="form-text">يستخدم لمنع تكرار نفس الكيان القانوني داخل المنشأة.</div>@error('tax_number')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-5"><label class="form-label">الرقم الوطني</label><input name="national_number" class="form-control" placeholder="الرقم الوطني للمنشأة إن وجد" value="{{ old('national_number', $contact->national_number) }}">
                    <div class="form-text">يساعد في تمييز الجهات التي لا تملك رقماً ضريبياً.</div>@error('national_number')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>
        </section>
        <section class="contact-section">
            <div class="contact-section-title">4. العنوان</div>
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">المدينة</label><input name="city" class="form-control" placeholder="مثال: عمّان" value="{{ old('city', $contact->city) }}">@error('city')<div class="text-danger small">{{ $message }}</div>@enderror</div>
                <div class="col-md-2"><label class="form-label">الدولة</label><input name="country" class="form-control" placeholder="JO" maxlength="2" value="{{ old('country', $contact->country ?: 'JO') }}">@error('country')<div class="text-danger small">{{ $message }}</div>@enderror</div>
                <div class="col-md-5"><label class="form-label">العنوان</label><textarea name="address" class="form-control" rows="3" placeholder="اكتب العنوان المختصر أو التفصيلي">{{ old('address', $contact->address) }}</textarea>@error('address')<div class="text-danger small">{{ $message }}</div>@enderror</div>
            </div>
        </section>
        <section class="contact-section">
            <div class="contact-section-title">5. الحالة</div>
            <div class="status-card"><label class="form-check mb-0"><input name="is_active" value="1" type="checkbox" class="form-check-input" @checked(old('is_active', $contact->is_active ?? true))> نشط ومتاح للاستخدام في الفواتير</label></div>
        </section>
    </div>
    <div class="contact-actions d-flex flex-wrap gap-2"><button class="btn btn-primary" name="save_action" value="save">{{ $mode === 'edit' ? 'حفظ التعديلات' : 'حفظ' }}</button>@if($mode !== 'edit')<button class="btn btn-outline-primary" name="save_action" value="save_another">حفظ وإضافة آخر</button>@endif<a class="btn btn-outline-secondary" href="{{ route('company.contacts.index', ['company' => $routeCompanyId]) }}">إلغاء</a></div>
</div>
