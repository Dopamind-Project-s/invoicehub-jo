<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $query = Blog::published()->with('author')->latest('published_at');
        $query->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('title_ar', 'like', '%'.$request->q.'%')->orWhere('excerpt_ar', 'like', '%'.$request->q.'%')));
        $query->when($request->filled('category'), fn ($q) => $q->where('category', $request->category));

        return view('blogs.index', [
            'posts' => $query->paginate(9)->withQueryString(),
            'featured' => Blog::published()->with('author')->where('is_featured', true)->latest('published_at')->first(),
            'categories' => Blog::published()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
            'settings' => SiteSetting::pluck('value', 'key')->all(),
        ]);
    }

    public function show(string $slug): View
    {
        $post = Blog::published()->with('author')->where('slug', $slug)->firstOrFail();
        $post->increment('views_count');

        return view('blogs.show', [
            'post' => $post,
            'related' => Blog::published()->with('author')->whereKeyNot($post->id)->where('category', $post->category)->latest('published_at')->limit(3)->get(),
            'settings' => SiteSetting::pluck('value', 'key')->all(),
        ]);
    }
}
