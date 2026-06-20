@csrf
<div class="row g-3" dir="rtl">
    <div class="col-md-6"><label class="form-label">اسم الباقة</label><input name="name" class="form-control" value="{{ old('name', $plan->name) }}" required></div>
    <div class="col-md-6"><label class="form-label">Slug</label><input name="slug" class="form-control" value="{{ old('slug', $plan->slug) }}"></div>
    <div class="col-12"><label class="form-label">الوصف</label><textarea name="description" class="form-control" rows="3">{{ old('description', $plan->description) }}</textarea></div>
    <div class="col-md-4"><label class="form-label">السعر الشهري</label><input name="monthly_price" type="number" step="0.001" min="0" class="form-control" value="{{ old('monthly_price', $plan->monthly_price ?? $plan->price ?? 0) }}" required></div>
    <div class="col-md-4"><label class="form-label">السعر السنوي</label><input name="yearly_price" type="number" step="0.001" min="0" class="form-control" value="{{ old('yearly_price', $plan->yearly_price ?? 0) }}" required></div>
    <div class="col-md-4"><label class="form-label">الحالة</label><select name="is_active" class="form-select"><option value="1" @selected(old('is_active', $plan->is_active ?? true))>فعالة</option><option value="0" @selected(! old('is_active', $plan->is_active ?? true))>معطلة</option></select></div>
</div>

<hr>
<h2 class="h5">مفاتيح المزايا ضمن الباقة</h2>
<div class="row g-2">
    @foreach($features as $feature)
        <div class="col-md-4">
            <label class="form-check border rounded p-3 h-100">
                <input class="form-check-input" type="checkbox" name="feature_keys[]" value="{{ $feature->id }}" @checked(in_array($feature->id, old('feature_keys', $enabledFeatureIds ?? [])))>
                <span class="form-check-label"><strong>{{ $feature->name_ar ?: $feature->name }}</strong><br><small class="text-muted">{{ $feature->code }} — {{ $feature->category }}</small></span>
            </label>
        </div>
    @endforeach
</div>
<button class="btn btn-primary mt-3">حفظ</button>
