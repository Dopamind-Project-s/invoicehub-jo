<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\LandingCms;

use App\Http\Controllers\Controller;
use App\Models\LandingFaq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LandingFaqController extends Controller
{
    public function index()
    {
        return view('admin.landing-cms.faqs.index', ['faqs' => LandingFaq::orderBy('sort_order')->paginate(20), 'faq' => new LandingFaq(['is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        LandingFaq::create($this->data($request));
        return back()->with('status', 'تمت إضافة السؤال الشائع.');
    }

    public function edit(LandingFaq $faq)
    {
        return view('admin.landing-cms.faqs.edit', compact('faq'));
    }

    public function update(Request $request, LandingFaq $faq): RedirectResponse
    {
        $faq->update($this->data($request));
        return redirect()->route('admin.landing-cms.faqs.index')->with('status', 'تم تحديث السؤال الشائع.');
    }

    public function destroy(LandingFaq $faq): RedirectResponse
    {
        $faq->delete();
        return back()->with('status', 'تم حذف السؤال الشائع.');
    }

    private function data(Request $request): array
    {
        $data = $request->validate([
            'question_ar' => ['required', 'string', 'max:255'],
            'question_en' => ['nullable', 'string', 'max:255'],
            'answer_ar' => ['required', 'string'],
            'answer_en' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:80'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_active'] = $request->boolean('is_active');
        return $data;
    }
}
