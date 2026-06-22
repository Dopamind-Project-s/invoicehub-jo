<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\CompanyWorkspace\MasterData\ContactController;
use App\Http\Controllers\CompanyWorkspace\MasterData\ProductCategoryController;
use App\Http\Controllers\CompanyWorkspace\MasterData\ProductController;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\TaxCategory;
use App\Models\TaxProfile;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MasterDataFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_data_model_relationships_are_company_scoped(): void
    {
        $company = Company::create(['legal_name_ar' => 'شركة', 'tax_number' => '9001']);
        $category = ProductCategory::create(['company_id' => $company->id, 'name_ar' => 'أجهزة', 'code' => 'DEV']);
        $unit = Unit::create(['company_id' => $company->id, 'code' => 'PCS', 'name' => 'قطعة', 'name_ar' => 'قطعة', 'symbol' => 'pc']);
        $taxProfile = TaxProfile::create(['company_id' => $company->id, 'name' => 'ضريبة عامة', 'tax_type' => 'sales', 'tax_percent' => 16]);
        $taxCategory = TaxCategory::create(['code' => 'S', 'tax_rate' => 16, 'tax_code' => 'S', 'description' => 'Sales']);
        $product = Product::create(['company_id' => $company->id, 'category_id' => $category->id, 'unit_id' => $unit->id, 'tax_profile_id' => $taxProfile->id, 'tax_category_id' => $taxCategory->id, 'type' => 'product', 'sku' => 'SKU-1', 'item_code' => 'SKU-1', 'name_ar' => 'منتج', 'price' => 10, 'default_price' => 10]);

        $this->assertTrue($category->company->is($company));
        $this->assertTrue($product->category->is($category));
        $this->assertTrue($product->unit->is($unit));
        $this->assertTrue($product->taxProfile->is($taxProfile));
        $this->assertTrue($taxProfile->products()->whereKey($product)->exists());
    }

    public function test_product_category_crud_validation_audit_and_company_isolation(): void
    {
        [$companyA, $companyB] = [Company::create(['legal_name_ar' => 'أ', 'tax_number' => '9002']), Company::create(['legal_name_ar' => 'ب', 'tax_number' => '9003'])];

        $controller = app(ProductCategoryController::class);
        $controller->store($this->request(['name_ar' => 'خدمات', 'code' => 'SERV', 'is_active' => '1']), $companyA);
        $controller->store($this->request(['name_ar' => 'خدمات', 'code' => 'SERV', 'is_active' => '1']), $companyB);
        try {
            $controller->store($this->request(['name_ar' => 'مكرر', 'code' => 'SERV']), $companyA);
            $this->fail('Duplicate category code should fail validation.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('code', $exception->errors());
        }

        $this->assertDatabaseHas('product_categories', ['company_id' => $companyA->id, 'code' => 'SERV']);
        $this->assertDatabaseHas('product_categories', ['company_id' => $companyB->id, 'code' => 'SERV']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'master_data.product_category.created']);
    }

    public function test_product_service_crud_uses_company_owned_foundation_records(): void
    {
        $company = Company::create(['legal_name_ar' => 'شركة', 'tax_number' => '9004']);
        $other = Company::create(['legal_name_ar' => 'أخرى', 'tax_number' => '9005']);
        $category = ProductCategory::create(['company_id' => $company->id, 'name_ar' => 'اشتراكات', 'code' => 'SUB']);
        $unit = Unit::create(['company_id' => $company->id, 'code' => 'MON', 'name' => 'شهر', 'name_ar' => 'شهر', 'symbol' => 'mo']);
        $otherUnit = Unit::create(['company_id' => $other->id, 'code' => 'OTH', 'name' => 'آخر', 'name_ar' => 'آخر']);
        $taxProfile = TaxProfile::create(['company_id' => $company->id, 'name' => 'صفرية', 'tax_type' => 'sales', 'tax_percent' => 0]);

        $controller = app(ProductController::class);
        $controller->store($this->request(['type' => 'service', 'sku' => 'SVC-1', 'name_ar' => 'خدمة شهرية', 'category_id' => $category->id, 'unit_id' => $unit->id, 'tax_profile_id' => $taxProfile->id, 'price' => 25, 'is_active' => '1']), $company);
        try {
            $controller->store($this->request(['type' => 'service', 'sku' => 'SVC-2', 'name_ar' => 'خدمة خاطئة', 'unit_id' => $otherUnit->id, 'price' => 25]), $company);
            $this->fail('Foreign company unit should fail validation.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('unit_id', $exception->errors());
        }

        $this->assertDatabaseHas('products', ['company_id' => $company->id, 'sku' => 'SVC-1', 'type' => 'service']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'master_data.product.created']);
    }



    public function test_product_pages_render_and_do_not_use_vite(): void
    {
        [$company, $product] = $this->productFixture();
        $this->withoutMiddleware(\Spatie\Permission\Middleware\PermissionMiddleware::class);
        $this->actingAs(\App\Models\User::factory()->create(['company_id' => $company->id]));

        $this->get(route('company.products.index', $company))->assertOk()->assertSee('المنتجات والخدمات');
        $this->get(route('company.products.create', $company))->assertOk()->assertSee('حفظ المنتج');
        $this->get(route('company.products.edit', [$company, $product]))->assertOk()->assertSee('حفظ التعديلات');

        foreach (glob(resource_path('views/company/master-data/products/*.blade.php')) ?: [] as $file) {
            $this->assertStringNotContainsString('@vite', file_get_contents($file), $file);
        }
    }

    public function test_product_can_be_created_and_updated_with_image(): void
    {
        Storage::fake('public');
        [$company, $product, $unit] = $this->productFixture();
        $controller = app(ProductController::class);

        $request = $this->request([
            'type' => 'product', 'sku' => 'IMG-1', 'name_ar' => 'منتج بصورة', 'unit_id' => $unit->id, 'price' => '12.500', 'is_active' => '1',
            'image' => UploadedFile::fake()->image('product.jpg', 600, 600)->size(250),
        ]);
        $controller->store($request, $company);
        $created = Product::where('company_id', $company->id)->where('sku', 'IMG-1')->firstOrFail();
        $this->assertNotNull($created->image_path);
        Storage::disk('public')->assertExists($created->image_path);

        $request = $this->request([
            'type' => 'product', 'sku' => 'IMG-1', 'name_ar' => 'منتج بصورة محدث', 'unit_id' => $unit->id, 'price' => '15.000', 'is_active' => '1',
            'image' => UploadedFile::fake()->image('updated.png', 600, 600)->size(250),
        ]);
        $controller->update($request, $company, $created);
        $created->refresh();
        $this->assertSame('منتج بصورة محدث', $created->name_ar);
        $this->assertNotNull($created->image_path);
        Storage::disk('public')->assertExists($created->image_path);
    }

    public function test_product_image_validation_rejects_invalid_type_and_large_file(): void
    {
        [$company, , $unit] = $this->productFixture();
        $controller = app(ProductController::class);

        foreach ([UploadedFile::fake()->create('bad.pdf', 100, 'application/pdf'), UploadedFile::fake()->image('large.jpg')->size(2500)] as $file) {
            try {
                $controller->store($this->request(['type' => 'product', 'sku' => 'BAD-'.uniqid(), 'name_ar' => 'سيء', 'unit_id' => $unit->id, 'price' => '1', 'image' => $file]), $company);
                $this->fail('Invalid image should fail validation.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('image', $exception->errors());
            }
        }
    }

    public function test_product_company_isolation_is_enforced_on_edit(): void
    {
        [$companyA, $product] = $this->productFixture();
        $companyB = Company::create(['legal_name_ar' => 'شركة ب', 'tax_number' => '9911']);
        $this->withoutMiddleware(\Spatie\Permission\Middleware\PermissionMiddleware::class);
        $this->actingAs(\App\Models\User::factory()->create(['company_id' => $companyA->id]));

        $this->get(route('company.products.edit', [$companyA, $product]))->assertOk();
        $this->get(route('company.products.edit', [$companyB, $product]))->assertNotFound();
    }

    public function test_contacts_avoid_duplicate_legal_entities_inside_company_and_audit_changes(): void
    {
        $company = Company::create(['legal_name_ar' => 'شركة', 'tax_number' => '9006']);

        $payload = ['type' => Contact::TYPE_CUSTOMER, 'name_ar' => 'عميل', 'tax_number' => '123456', 'phone' => '0790000000', 'country' => 'JO', 'is_active' => '1'];
        $controller = app(ContactController::class);
        $controller->store($this->request($payload), $company);
        $controller->store($this->request($payload + ['name_ar' => 'عميل محدث', 'email' => 'customer@example.com']), $company);

        $this->assertSame(1, Contact::where('company_id', $company->id)->where('tax_number', '123456')->count());
        $this->assertDatabaseHas('contacts', ['company_id' => $company->id, 'tax_number' => '123456', 'email' => 'customer@example.com']);
        $this->assertTrue(AuditLog::whereIn('action', ['master_data.contact.created', 'master_data.contact.updated'])->count() >= 2);
    }


    /** @return array{0: Company, 1: Product, 2: Unit} */
    private function productFixture(): array
    {
        $company = Company::create(['legal_name_ar' => 'شركة منتجات', 'tax_number' => (string) random_int(100000, 999999)]);
        $unit = Unit::create(['company_id' => $company->id, 'code' => 'PCS'.random_int(100,999), 'name' => 'قطعة', 'name_ar' => 'قطعة', 'symbol' => 'pc']);
        $taxCategory = TaxCategory::first() ?: TaxCategory::create(['code' => 'Z', 'tax_rate' => 0, 'tax_code' => 'Z', 'description' => 'Zero']);
        $product = Product::create(['company_id' => $company->id, 'unit_id' => $unit->id, 'tax_category_id' => $taxCategory->id, 'type' => 'product', 'sku' => 'SKU-'.uniqid(), 'item_code' => 'ITM-'.uniqid(), 'name_ar' => 'منتج تجريبي', 'price' => 10, 'default_price' => 10, 'is_active' => true]);

        return [$company, $product, $unit];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function request(array $data): Request
    {
        $request = Request::create('/master-data-test', 'POST', [], [], array_filter($data, fn ($value) => $value instanceof UploadedFile));
        $request->merge(array_filter($data, fn ($value) => ! $value instanceof UploadedFile));
        return $request;
    }
}
