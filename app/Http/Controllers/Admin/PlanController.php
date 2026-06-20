<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PlanController extends Controller
{
    public function index(){ return view('admin.plans.index', ['plans' => Plan::latest()->paginate(15), 'plan' => new Plan(['billing_cycle' => 'monthly', 'is_active' => true])]); }
    public function store(Request $request): RedirectResponse { Plan::create($this->data($request)); return back()->with('status', 'تم إنشاء الباقة.'); }
    public function edit(Plan $plan){ return view('admin.plans.edit', compact('plan')); }
    public function update(Request $request, Plan $plan): RedirectResponse { $plan->update($this->data($request, $plan)); return redirect()->route('admin.plans.index')->with('status', 'تم تحديث الباقة.'); }
    public function activate(Plan $plan): RedirectResponse { $plan->update(['is_active' => true]); return back()->with('status', 'تم تفعيل الباقة.'); }
    public function deactivate(Plan $plan): RedirectResponse { $plan->update(['is_active' => false]); return back()->with('status', 'تم تعطيل الباقة.'); }
    private function data(Request $request, ?Plan $plan = null): array { $data = $request->validate(['name'=>['required','string','max:255'], 'slug'=>['nullable','string','max:255', Rule::unique('plans','slug')->ignore($plan)], 'price'=>['required','numeric','min:0'], 'billing_cycle'=>['required','string','max:30'], 'is_active'=>['nullable','boolean']]); $data['slug'] = $data['slug'] ?: Str::slug($data['name']); $data['is_active'] = $request->boolean('is_active'); return $data; }
}
