@csrf
<div class="row g-3" dir="rtl">
 <div class="col-md-6"><label class="form-label">العنوان العربي</label><input name="title_ar" class="form-control" value="{{ old('title_ar',$post->title_ar) }}" required></div>
 <div class="col-md-6"><label class="form-label">English Title</label><input name="title_en" class="form-control" value="{{ old('title_en',$post->title_en) }}"></div>
 <div class="col-md-4"><label class="form-label">Slug</label><input name="slug" class="form-control" value="{{ old('slug',$post->slug) }}" placeholder="jordan-e-invoicing-guide"></div>
 <div class="col-md-3"><label class="form-label">التصنيف</label><input name="category" class="form-control" value="{{ old('category',$post->category) }}"></div>
 <div class="col-md-2"><label class="form-label">الحالة</label><select name="status" class="form-select">@foreach(\App\Models\Blog::statuses() as $key=>$label)<option value="{{ $key }}" @selected(old('status',$post->status)===$key)>{{ $label }}</option>@endforeach</select></div>
 <div class="col-md-3"><label class="form-label">تاريخ النشر</label><input type="datetime-local" name="published_at" class="form-control" value="{{ old('published_at', optional($post->published_at)->format('Y-m-d\TH:i')) }}"></div>
 <div class="col-12"><label class="form-check"><input type="checkbox" name="is_featured" value="1" class="form-check-input" @checked(old('is_featured',$post->is_featured))> <span class="form-check-label">مقال مميز</span></label></div>
 <div class="col-md-6"><label class="form-label">المختصر العربي</label><textarea name="excerpt_ar" class="form-control" rows="3">{{ old('excerpt_ar',$post->excerpt_ar) }}</textarea></div>
 <div class="col-md-6"><label class="form-label">English Excerpt</label><textarea name="excerpt_en" class="form-control" rows="3">{{ old('excerpt_en',$post->excerpt_en) }}</textarea></div>
 <div class="col-12"><label class="form-label">المحتوى العربي</label><textarea name="content_ar" class="form-control" rows="14" required>{{ old('content_ar',$post->content_ar) }}</textarea></div>
 <div class="col-12"><label class="form-label">English Content</label><textarea name="content_en" class="form-control" rows="8">{{ old('content_en',$post->content_en) }}</textarea></div>
 <div class="col-md-6"><label class="form-label">صورة المقال</label><input type="file" name="image" accept="image/*" class="form-control" onchange="document.getElementById('blogPreview').src=window.URL.createObjectURL(this.files[0])"></div>
 <div class="col-md-6">@if($post->image)<img id="blogPreview" class="admin-blog-preview" src="{{ $post->image_url }}" alt="preview">@else<img id="blogPreview" class="admin-blog-preview d-none" alt="preview" onload="this.classList.remove('d-none')">@endif</div>
 <div class="col-md-4"><label class="form-label">Tags (comma separated)</label><input name="tags" class="form-control" value="{{ old('tags', implode(', ', $post->tags ?? [])) }}"></div>
 <div class="col-md-4"><label class="form-label">Meta Title</label><input name="meta_title" class="form-control" value="{{ old('meta_title',$post->meta_title) }}"></div>
 <div class="col-md-4"><label class="form-label">Meta Description</label><input name="meta_description" class="form-control" value="{{ old('meta_description',$post->meta_description) }}"></div>
 <div class="col-12 d-flex gap-2"><button class="btn btn-primary">حفظ</button><a class="btn btn-outline-secondary" href="{{ route('admin.blogs.index') }}">عودة</a></div>
</div>
