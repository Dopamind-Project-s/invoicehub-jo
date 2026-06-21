@csrf
<div class="row g-3" dir="rtl">
<div class="col-md-3"><label class="form-label">النوع</label><select name="type" class="form-select"><option value="product" @selected(old('type', $product->type)==='product')>منتج</option><option value="service" @selected(old('type', $product->type)==='service')>خدمة</option></select></div>
<div class="col-md-3"><label class="form-label">SKU</label><input name="sku" class="form-control" value="{{ old('sku', $product->sku) }}" required></div>
<div class="col-md-3"><label class="form-label">الباركود</label><input name="barcode" class="form-control" value="{{ old('barcode', $product->barcode) }}"></div>
<div class="col-md-3"><label class="form-check mt-4"><input name="is_active" value="1" type="checkbox" class="form-check-input" @checked(old('is_active', $product->is_active ?? true))> نشط</label></div>
<div class="col-md-6"><label class="form-label">الاسم العربي</label><input name="name_ar" class="form-control" value="{{ old('name_ar', $product->name_ar) }}" required></div>
<div class="col-md-6"><label class="form-label">الاسم الإنجليزي</label><input name="name_en" class="form-control" value="{{ old('name_en', $product->name_en) }}"></div>
<div class="col-md-4"><label class="form-label">التصنيف</label><select name="category_id" class="form-select"><option value="">بدون</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id)===(string) $category->id)>{{ $category->name_ar }}</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">الوحدة</label><select name="unit_id" class="form-select" required>@foreach($units as $unit)<option value="{{ $unit->id }}" @selected((string) old('unit_id', $product->unit_id)===(string) $unit->id)>{{ $unit->name_ar }} ({{ $unit->code }})</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">ملف الضريبة</label><select name="tax_profile_id" class="form-select"><option value="">بدون</option>@foreach($taxProfiles as $taxProfile)<option value="{{ $taxProfile->id }}" @selected((string) old('tax_profile_id', $product->tax_profile_id)===(string) $taxProfile->id)>{{ $taxProfile->name }}</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label">السعر</label><input name="price" type="number" step="0.000001" class="form-control" value="{{ old('price', $product->price) }}" required></div>
<div class="col-md-3"><label class="form-label">التكلفة</label><input name="cost" type="number" step="0.000001" class="form-control" value="{{ old('cost', $product->cost) }}"></div>
<div class="col-md-12"><label class="form-label">الوصف</label><textarea name="description" class="form-control">{{ old('description', $product->description) }}</textarea></div>
</div><button class="btn btn-primary mt-4">حفظ</button>
