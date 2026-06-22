<?php

declare(strict_types=1);

namespace App\Http\Controllers\CompanyWorkspace\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\TaxCategory;
use App\Models\TaxProfile;
use App\Models\Unit;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index(Request $request, Company $company)
    {
        $products = Product::with(['category', 'unit', 'taxProfile'])->where('company_id', $company->id)
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($s) => $s->where('name_ar', 'like', '%'.$request->search.'%')->orWhere('name_en', 'like', '%'.$request->search.'%')->orWhere('sku', 'like', '%'.$request->search.'%')->orWhere('barcode', 'like', '%'.$request->search.'%')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->latest()->paginate(15)->withQueryString();

        return view('company.master-data.products.index', compact('company', 'products'));
    }

    public function create(Company $company)
    {
        return view('company.master-data.products.create', $this->formData($company, new Product(['is_active' => true, 'type' => Product::TYPE_PRODUCT])));
    }

    public function store(Request $request, Company $company): RedirectResponse
    {
        $data = $this->validated($request, $company);
        $data['image_path'] = $this->storeImage($request);
        $product = Product::create($data + [
            'company_id' => $company->id,
            'item_code' => $company->id.'-'.$data['sku'],
            'default_price' => $data['price'],
            'tax_category_id' => TaxCategory::query()->value('id') ?? TaxCategory::create(['code' => 'MASTER_DEFAULT', 'tax_rate' => 0, 'tax_code' => 'Z', 'description' => 'Master data fallback tax category'])->id,
        ]);
        $this->audit->record('master_data.product.created', $product, [], $product->toArray(), $request);

        if ($request->input('save_action') === 'save_another') {
            return redirect()->route('company.products.create', $company)->with('status', 'تم حفظ المنتج، يمكنك إضافة منتج آخر.');
        }

        return redirect()->route('company.products.index', $company)->with('status', 'تم إنشاء المنتج/الخدمة.');
    }

    public function edit(Company $company, Product $product)
    {
        abort_unless((int) $product->company_id === (int) $company->id, 404);
        return view('company.master-data.products.edit', $this->formData($company, $product));
    }

    public function update(Request $request, Company $company, Product $product): RedirectResponse
    {
        abort_unless((int) $product->company_id === (int) $company->id, 404);
        $before = $product->toArray();
        $data = $this->validated($request, $company, $product);
        if ($request->hasFile('image')) {
            $data['image_path'] = $this->storeImage($request, $product->image_path);
        }

        $product->update($data + ['item_code' => $company->id.'-'.$data['sku'], 'default_price' => $data['price']]);
        $this->audit->record('master_data.product.updated', $product, $before, $product->toArray(), $request);
        return redirect()->route('company.products.index', $company)->with('status', 'تم تحديث المنتج/الخدمة.');
    }

    public function activate(Request $request, Company $company, Product $product): RedirectResponse { return $this->setActive($request, $company, $product, true); }
    public function deactivate(Request $request, Company $company, Product $product): RedirectResponse { return $this->setActive($request, $company, $product, false); }

    private function setActive(Request $request, Company $company, Product $product, bool $active): RedirectResponse
    {
        abort_unless((int) $product->company_id === (int) $company->id, 404);
        $before = $product->only('is_active');
        $product->update(['is_active' => $active]);
        $this->audit->record('master_data.product.'.($active ? 'activated' : 'deactivated'), $product, $before, $product->only('is_active'), $request);
        return back()->with('status', $active ? 'تم تفعيل المنتج.' : 'تم تعطيل المنتج.');
    }

    private function formData(Company $company, Product $product): array
    {
        return ['company' => $company, 'product' => $product, 'categories' => ProductCategory::where('company_id', $company->id)->where('is_active', true)->orderBy('name_ar')->get(), 'units' => Unit::where(fn ($q) => $q->whereNull('company_id')->orWhere('company_id', $company->id))->where('is_active', true)->orderBy('code')->get(), 'taxProfiles' => TaxProfile::where(fn ($q) => $q->whereNull('company_id')->orWhere('company_id', $company->id))->where('is_active', true)->orderBy('name')->get()];
    }

    private function validated(Request $request, Company $company, ?Product $product = null): array
    {
        return $request->validate([
            'type' => ['required', Rule::in([Product::TYPE_PRODUCT, Product::TYPE_SERVICE])],
            'sku' => ['required', 'string', 'max:100', Rule::unique('products', 'sku')->where('company_id', $company->id)->ignore($product)],
            'barcode' => ['nullable', 'string', 'max:100'],
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', Rule::exists('product_categories', 'id')->where('company_id', $company->id)],
            'unit_id' => ['required', Rule::exists('units', 'id')->where(fn ($q) => $q->whereNull('company_id')->orWhere('company_id', $company->id))],
            'tax_profile_id' => ['nullable', Rule::exists('tax_profiles', 'id')->where(fn ($q) => $q->whereNull('company_id')->orWhere('company_id', $company->id))],
            'price' => ['required', 'numeric', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'save_action' => ['nullable', Rule::in(['save', 'save_another'])],
        ]) + ['is_active' => $request->boolean('is_active')];
    }

    private function storeImage(Request $request, ?string $oldPath = null): ?string
    {
        if (! $request->hasFile('image')) {
            return $oldPath;
        }

        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return $request->file('image')->store('products', 'public');
    }
}
