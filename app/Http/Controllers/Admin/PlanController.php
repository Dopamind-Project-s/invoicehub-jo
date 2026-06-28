<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeatureKey;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PlanController extends Controller
{
    public function index()
    {
        return view('admin.plans.index', [
            'plans' => Plan::query()
                ->with('featureKeys')
                ->withCount('featureKeys')
                ->when(request('search'), fn ($query, $search) => $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('name_ar', 'like', "%{$search}%")->orWhere('description_ar', 'like', "%{$search}%")))
                ->when(request('status') === 'active', fn ($query) => $query->where('is_active', true))
                ->when(request('status') === 'inactive', fn ($query) => $query->where('is_active', false))
                ->when(request('recommended') === '1', fn ($query) => $query->where('is_recommended', true))
                ->orderBy('sort_order')
                ->latest()
                ->paginate(12)
                ->withQueryString(),
            'plan' => new Plan(['billing_cycle' => 'monthly', 'is_active' => true, 'monthly_price' => 0, 'yearly_price' => 0, 'sort_order' => 0]),
            'features' => FeatureKey::where('is_active', true)->orderBy('category')->orderBy('code')->get(),
            'enabledFeatureIds' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        [$data, $featureIds] = $this->data($request);
        $plan = Plan::create($data);
        $plan->featureKeys()->sync($featureIds);

        return back()->with('status', 'تم إنشاء الباقة وربط مفاتيح المزايا.');
    }

    public function edit(Plan $plan)
    {
        return view('admin.plans.edit', [
            'plan' => $plan->load('featureKeys'),
            'features' => FeatureKey::where('is_active', true)->orderBy('category')->orderBy('code')->get(),
            'enabledFeatureIds' => $plan->featureKeys->pluck('id')->all(),
        ]);
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        [$data, $featureIds] = $this->data($request, $plan);
        $plan->update($data);
        $plan->featureKeys()->sync($featureIds);

        return redirect()->route('admin.plans.index')->with('status', 'تم تحديث الباقة ومزاياها.');
    }

    public function activate(Plan $plan): RedirectResponse
    {
        $plan->update(['is_active' => true]);

        return back()->with('status', 'تم تفعيل الباقة.');
    }

    public function deactivate(Plan $plan): RedirectResponse
    {
        $plan->update(['is_active' => false]);

        return back()->with('status', 'تم تعطيل الباقة.');
    }

    /** @return array{0: array<string,mixed>, 1: array<int,int>} */
    private function data(Request $request, ?Plan $plan = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('plans', 'slug')->ignore($plan)],
            'description' => ['nullable', 'string', 'max:2000'],
            'description_ar' => ['nullable', 'string', 'max:2000'],
            'description_en' => ['nullable', 'string', 'max:2000'],
            'monthly_price' => ['required', 'numeric', 'min:0'],
            'yearly_price' => ['required', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_recommended' => ['nullable', 'boolean'],
            'feature_keys' => ['array'],
            'feature_keys.*' => ['integer', 'exists:feature_keys,id'],
        ]);

        $featureIds = array_map('intval', $data['feature_keys'] ?? []);
        unset($data['feature_keys']);

        $data['name_ar'] = $data['name_ar'] ?: $data['name'];
        $data['description_ar'] = $data['description_ar'] ?: ($data['description'] ?? null);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name_en'] ?: $data['name']);
        $data['price'] = $data['monthly_price'];
        $data['billing_cycle'] = 'monthly';
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_active'] = $request->boolean('is_active');
        $data['is_recommended'] = $request->boolean('is_recommended');

        return [$data, $featureIds];
    }
}
