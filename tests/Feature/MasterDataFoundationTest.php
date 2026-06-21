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
    /**
     * @param array<string, mixed> $data
     */
    private function request(array $data): Request
    {
        $request = Request::create('/master-data-test', 'POST', $data);
        return $request;
    }
}
