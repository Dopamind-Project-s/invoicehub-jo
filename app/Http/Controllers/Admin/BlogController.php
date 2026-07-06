<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $posts = Blog::withTrashed()->with('author')->when($request->status, fn ($q, $s) => $q->where('status', $s))->when($request->q, fn ($q, $s) => $q->where('title_ar', 'like', "%{$s}%"))->latest()->paginate(15)->withQueryString();
        return view('admin.blogs.index', compact('posts'));
    }

    public function create(): View { return view('admin.blogs.create', ['post' => new Blog(['status' => Blog::STATUS_DRAFT, 'published_at' => now()])]); }
    public function store(Request $request): RedirectResponse { $post = Blog::create($this->data($request) + ['created_by' => $request->user()->id]); return redirect()->route('admin.blogs.edit', $post)->with('success', 'تم إنشاء المقال.'); }
    public function show(Blog $blog): RedirectResponse { return redirect()->route('admin.blogs.edit', $blog); }
    public function edit(Blog $blog): View { return view('admin.blogs.edit', ['post' => $blog]); }
    public function update(Request $request, Blog $blog): RedirectResponse { $blog->update($this->data($request, $blog)); return back()->with('success', 'تم تحديث المقال.'); }
    public function destroy(Blog $blog): RedirectResponse { $blog->delete(); return back()->with('success', 'تم حذف المقال ونقله للأرشيف.'); }
    public function restore(int $id): RedirectResponse { Blog::withTrashed()->findOrFail($id)->restore(); return back()->with('success', 'تمت استعادة المقال.'); }
    public function publish(Blog $blog): RedirectResponse { $blog->update(['status' => Blog::STATUS_PUBLISHED, 'published_at' => $blog->published_at ?: now()]); return back()->with('success', 'تم نشر المقال.'); }
    public function draft(Blog $blog): RedirectResponse { $blog->update(['status' => Blog::STATUS_DRAFT]); return back()->with('success', 'تم تحويل المقال إلى مسودة.'); }

    private function data(Request $request, ?Blog $blog = null): array
    {
        $data = $request->validate([
            'title_ar' => ['required','string','max:255'], 'title_en' => ['nullable','string','max:255'],
            'slug' => ['nullable','string','max:255', Rule::unique('blogs','slug')->ignore($blog)],
            'excerpt_ar' => ['nullable','string','max:1000'], 'excerpt_en' => ['nullable','string','max:1000'],
            'content_ar' => ['required','string'], 'content_en' => ['nullable','string'], 'image' => ['nullable','image','max:3072'],
            'category' => ['nullable','string','max:120'], 'tags' => ['nullable','string','max:500'],
            'status' => ['required', Rule::in(array_keys(Blog::statuses()))], 'is_featured' => ['nullable','boolean'],
            'published_at' => ['nullable','date'], 'meta_title' => ['nullable','string','max:255'], 'meta_description' => ['nullable','string','max:500'],
        ]);
        $data['slug'] = $data['slug'] ?: Str::slug($data['title_en'] ?: Str::limit($data['title_ar'], 60, ''));
        if (! $data['slug']) { $data['slug'] = Str::slug(Str::ascii($data['title_ar'])).'-'.Str::random(5); }
        $data['tags'] = collect(explode(',', $data['tags'] ?? ''))->map(fn ($v) => trim($v))->filter()->values()->all();
        $data['is_featured'] = $request->boolean('is_featured');
        if ($request->hasFile('image')) { $data['image'] = $request->file('image')->store('blogs', 'public'); }
        return $data;
    }
}
