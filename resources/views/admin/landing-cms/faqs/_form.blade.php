@csrf
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">السؤال عربي</label><input name="question_ar" class="form-control" value="{{ old('question_ar', $faq->question_ar) }}" required></div>
    <div class="col-md-6"><label class="form-label">السؤال إنجليزي</label><input name="question_en" class="form-control" value="{{ old('question_en', $faq->question_en) }}"></div>
    <div class="col-md-6"><label class="form-label">الإجابة عربي</label><textarea name="answer_ar" class="form-control" rows="4" required>{{ old('answer_ar', $faq->answer_ar) }}</textarea></div>
    <div class="col-md-6"><label class="form-label">الإجابة إنجليزي</label><textarea name="answer_en" class="form-control" rows="4">{{ old('answer_en', $faq->answer_en) }}</textarea></div>
    <div class="col-md-4"><label class="form-label">التصنيف</label><input name="category" class="form-control" value="{{ old('category', $faq->category) }}"></div>
    <div class="col-md-4"><label class="form-label">الترتيب</label><input name="sort_order" type="number" min="0" class="form-control" value="{{ old('sort_order', $faq->sort_order ?? 0) }}"></div>
    <div class="col-md-4"><label class="form-label">الحالة</label><select name="is_active" class="form-select"><option value="1" @selected(old('is_active', $faq->is_active ?? true))>فعال</option><option value="0" @selected(! old('is_active', $faq->is_active ?? true))>مخفي</option></select></div>
</div>
<button class="btn btn-primary mt-3">حفظ</button>
