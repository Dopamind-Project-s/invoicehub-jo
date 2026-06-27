<?php

declare(strict_types=1);

namespace App\Http\Controllers\CompanyWorkspace\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\ProductCategory;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductCategoryController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index(Request $request, Company $company)
    {
        $categories = ProductCategory::query()->where('company_id', $company->id)
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($s) => $s->where('name_ar', 'like', '%'.$request->search.'%')->orWhere('name_en', 'like', '%'.$request->search.'%')->orWhere('code', 'like', '%'.$request->search.'%')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->orderBy('name_ar')->paginate(15)->withQueryString();
        return view('company.master-data.categories.index', compact('company', 'categories'));
    }

    public function create(Company $company) { return view('company.master-data.categories.create', ['company' => $company, 'category' => new ProductCategory(['is_active' => true])]); }

    public function store(Request $request, Company $company)
    {
        $data = $this->validated($request, $company);
        $category = ProductCategory::create($data + ['company_id' => $company->id]);
        $this->audit->record('master_data.product_category.created', $category, [], $category->toArray(), $request);
        if ($request->input('save_action') === 'save_another') {
            return redirect()->route('company.product-categories.create', $company)->with('status', 'تم حفظ الفئة، يمكنك إضافة فئة أخرى.');
        }

        return redirect()->route('company.product-categories.index', $company)->with('status', 'تم إنشاء التصنيف.');
    }

    public function edit(Company $company, ProductCategory $productCategory)
    {
        abort_unless((int) $productCategory->company_id === (int) $company->id, 404);
        return view('company.master-data.categories.edit', ['company' => $company, 'category' => $productCategory]);
    }

    public function update(Request $request, Company $company, ProductCategory $productCategory)
    {
        abort_unless((int) $productCategory->company_id === (int) $company->id, 404);
        $before = $productCategory->toArray();
        $productCategory->update($this->validated($request, $company, $productCategory));
        $this->audit->record('master_data.product_category.updated', $productCategory, $before, $productCategory->toArray(), $request);
        return redirect()->route('company.product-categories.index', $company)->with('status', 'تم تحديث التصنيف.');
    }

    public function activate(Request $request, Company $company, ProductCategory $productCategory)
    {
        abort_unless((int) $productCategory->company_id === (int) $company->id, 404);
        $before = $productCategory->only('is_active'); $productCategory->update(['is_active' => true]);
        $this->audit->record('master_data.product_category.activated', $productCategory, $before, $productCategory->only('is_active'), $request);
        return back();
    }

    public function deactivate(Request $request, Company $company, ProductCategory $productCategory)
    {
        abort_unless((int) $productCategory->company_id === (int) $company->id, 404);
        $before = $productCategory->only('is_active'); $productCategory->update(['is_active' => false]);
        $this->audit->record('master_data.product_category.deactivated', $productCategory, $before, $productCategory->only('is_active'), $request);
        return back();
    }

    private function validated(Request $request, Company $company, ?ProductCategory $category = null): array
    {
        return $request->validate([
            'name_ar' => ['required', 'string', 'max:255', 'not_regex:/^\s*$/u'], 'name_en' => ['nullable', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'not_regex:/^\s*$/u', Rule::unique('product_categories', 'code')->where('company_id', $company->id)->ignore($category)],
            'description' => ['nullable', 'string'], 'icon' => ['nullable', 'string', 'max:20'], 'is_active' => ['nullable', 'boolean'],
            'save_action' => ['nullable', Rule::in(['save', 'save_another'])],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
