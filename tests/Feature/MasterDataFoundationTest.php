<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\CompanyWorkspace\InvoiceEngineController;
use App\Http\Controllers\CompanyWorkspace\MasterData\ContactController;
use App\Http\Controllers\CompanyWorkspace\MasterData\ProductCategoryController;
use App\Http\Controllers\CompanyWorkspace\MasterData\ProductController;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\TaxCategory;
use App\Models\TaxProfile;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
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

    public function test_company_settings_profile_and_dashboard_render_without_secrets(): void
    {
        $company = Company::create(['legal_name_ar' => 'شركة لوحة', 'name_ar' => 'شركة لوحة', 'tax_number' => '770001', 'phone' => '0790000000', 'city' => 'عمّان', 'jofotara_client_id' => 'client-secret-value', 'jofotara_secret_key' => 'super-secret-key', 'jofotara_source_id' => 'SRC-1']);
        $user = \App\Models\User::factory()->create(['company_id' => $company->id]);
        $this->withoutMiddleware(\Spatie\Permission\Middleware\PermissionMiddleware::class);
        $this->actingAs($user);

        $this->get(route('company.settings.edit', $company))->assertOk()->assertSee('إعدادات المنشأة')->assertSee('حالة بيانات الربط')->assertSee('محفوظة ومكتملة')->assertDontSee('client-secret-value')->assertDontSee('super-secret-key');
        $this->get(route('company.dashboard', $company))->assertOk()->assertSee('لوحة تحكم المنشأة')->assertSee('ملف المنشأة')->assertSee('إنشاء فاتورة');
        $this->get(route('dashboard'))->assertOk()->assertSee('لوحة تحكم المنشأة')->assertDontSee('super-secret-key');
    }

    public function test_company_dashboard_stats_are_company_scoped_and_cache_invalidates(): void
    {
        $companyA = Company::create(['legal_name_ar' => 'شركة كاش أ', 'tax_number' => (string) random_int(100000, 999999), 'default_currency' => 'JOD']);
        $companyB = Company::create(['legal_name_ar' => 'شركة كاش ب', 'tax_number' => (string) random_int(100000, 999999), 'default_currency' => 'JOD']);
        $unit = Unit::create(['company_id' => $companyA->id, 'code' => 'DASH'.random_int(100,999), 'name' => 'قطعة', 'name_ar' => 'قطعة']);
        $taxCategory = TaxCategory::first() ?: TaxCategory::create(['code' => 'Z', 'tax_rate' => 0, 'tax_code' => 'Z', 'description' => 'Zero']);
        Product::create(['company_id' => $companyB->id, 'unit_id' => Unit::create(['company_id' => $companyB->id, 'code' => 'B'.random_int(100,999), 'name' => 'قطعة', 'name_ar' => 'قطعة'])->id, 'tax_category_id' => $taxCategory->id, 'type' => 'product', 'sku' => 'B-DASH', 'item_code' => 'B-DASH', 'name_ar' => 'منتج ب', 'price' => 3, 'default_price' => 3, 'is_active' => true]);
        $service = app(\App\Services\CompanyWorkspace\CompanyDashboardStatsService::class);

        $this->assertSame(0, $service->get($companyA)['product_count']);
        $this->assertSame(1, $service->get($companyB)['product_count']);

        Cache::put(\App\Services\CompanyWorkspace\CompanyDashboardStatsService::key($companyA), ['stale' => true], 600);
        app(ProductController::class)->store($this->request(['type' => 'product', 'sku' => 'CACHE-1', 'name_ar' => 'منتج كاش', 'unit_id' => $unit->id, 'price' => '5', 'is_active' => '1']), $companyA);
        $this->assertFalse(Cache::has(\App\Services\CompanyWorkspace\CompanyDashboardStatsService::key($companyA)));

        Cache::put(\App\Services\CompanyWorkspace\CompanyDashboardStatsService::key($companyA), ['stale' => true], 600);
        app(ContactController::class)->store($this->request(['type' => Contact::TYPE_CUSTOMER, 'name_ar' => 'عميل كاش', 'tax_number' => 'CACHE-C', 'country' => 'JO', 'is_active' => '1']), $companyA);
        $this->assertFalse(Cache::has(\App\Services\CompanyWorkspace\CompanyDashboardStatsService::key($companyA)));

        $contact = Contact::where('company_id', $companyA->id)->firstOrFail();
        Cache::put(\App\Services\CompanyWorkspace\CompanyDashboardStatsService::key($companyA), ['stale' => true], 600);
        app(InvoiceEngineController::class)->store($this->request(['contact_id' => $contact->id, 'invoice_type' => Invoice::TYPE_TAX_INVOICE, 'issue_date' => now()->toDateString(), 'due_date' => now()->addDay()->toDateString(), 'currency' => 'JOD', 'save_action' => 'draft', 'items' => [['description' => 'خدمة كاش', 'quantity' => 1, 'unit_price' => 10, 'discount_amount' => 0, 'tax_percent' => 0]]]), $companyA);
        $this->assertFalse(Cache::has(\App\Services\CompanyWorkspace\CompanyDashboardStatsService::key($companyA)));
    }


    public function test_contact_pages_render_and_do_not_use_vite(): void
    {
        $company = Company::create(['legal_name_ar' => 'شركة جهات', 'tax_number' => (string) random_int(100000, 999999)]);
        $contact = Contact::create(['company_id' => $company->id, 'type' => Contact::TYPE_CUSTOMER, 'name_ar' => 'عميل اختبار', 'tax_number' => 'TAX-'.random_int(100, 999), 'phone' => '0790000000', 'email' => 'client@example.com', 'city' => 'عمّان', 'country' => 'JO', 'is_active' => true]);
        $this->withoutMiddleware(\Spatie\Permission\Middleware\PermissionMiddleware::class);
        $this->actingAs(\App\Models\User::factory()->create(['company_id' => $company->id]));

        $this->get(route('company.contacts.index', $company))->assertOk()->assertSee('العملاء والموردون')->assertSee('أدر بيانات العملاء والموردين لاستخدامها في الفواتير.');
        $this->get(route('company.contacts.create', $company))->assertOk()->assertSee('حفظ وإضافة آخر')->assertSee('البيانات الضريبية');
        $this->get(route('company.contacts.edit', [$company, $contact]))->assertOk()->assertSee('آخر تحديث')->assertSee('تعطيل جهة الاتصال');

        foreach (glob(resource_path('views/company/master-data/contacts/*.blade.php')) ?: [] as $file) {
            $this->assertStringNotContainsString('@vite', file_get_contents($file), $file);
        }
    }

    public function test_contact_create_update_duplicate_prevention_and_company_isolation(): void
    {
        $companyA = Company::create(['legal_name_ar' => 'شركة العملاء أ', 'tax_number' => (string) random_int(100000, 999999)]);
        $companyB = Company::create(['legal_name_ar' => 'شركة العملاء ب', 'tax_number' => (string) random_int(100000, 999999)]);
        $controller = app(ContactController::class);

        $controller->store($this->request(['type' => Contact::TYPE_CUSTOMER, 'name_ar' => 'عميل جديد', 'tax_number' => 'DUP-100', 'phone' => '0791111111', 'email' => 'new@example.com', 'city' => 'إربد', 'country' => 'JO', 'is_active' => '1']), $companyA);
        $created = Contact::where('company_id', $companyA->id)->where('tax_number', 'DUP-100')->firstOrFail();
        $this->assertSame('عميل جديد', $created->name_ar);

        $controller->update($this->request(['type' => Contact::TYPE_BOTH, 'name_ar' => 'عميل محدث', 'tax_number' => 'DUP-100', 'phone' => '0792222222', 'email' => 'updated@example.com', 'city' => 'عمّان', 'country' => 'JO', 'is_active' => '1']), $companyA, $created);
        $created->refresh();
        $this->assertSame(Contact::TYPE_BOTH, $created->type);
        $this->assertSame('عميل محدث', $created->name_ar);

        Contact::create(['company_id' => $companyA->id, 'type' => Contact::TYPE_SUPPLIER, 'name_ar' => 'مورد مكرر', 'tax_number' => 'DUP-200', 'country' => 'JO', 'is_active' => true]);
        $response = $controller->update($this->request(['type' => Contact::TYPE_CUSTOMER, 'name_ar' => 'تكرار', 'tax_number' => 'DUP-200', 'country' => 'JO', 'is_active' => '1']), $companyA, $created);
        $this->assertTrue($response->getSession()->get('errors')->has('tax_number'));

        $this->withoutMiddleware(\Spatie\Permission\Middleware\PermissionMiddleware::class);
        $this->actingAs(\App\Models\User::factory()->create(['company_id' => $companyA->id]));
        $this->get(route('company.contacts.edit', [$companyA, $created]))->assertOk();
        $this->get(route('company.contacts.edit', [$companyB, $created]))->assertNotFound();
    }


    public function test_units_tax_profiles_and_activity_pages_render(): void
    {
        $company = Company::create(['legal_name_ar' => 'شركة تشغيل', 'tax_number' => (string) random_int(100000, 999999)]);
        $user = \App\Models\User::factory()->create(['company_id' => $company->id, 'name' => 'مدير التشغيل']);
        $globalUnit = Unit::create(['code' => 'KG'.random_int(100, 999), 'name' => 'Kilogram', 'name_ar' => 'كيلوغرام', 'symbol' => 'kg', 'is_active' => true]);
        $unit = Unit::create(['company_id' => $company->id, 'code' => 'BOX'.random_int(100, 999), 'name' => 'Box', 'name_ar' => 'صندوق', 'symbol' => 'box', 'is_active' => true]);
        $taxProfile = TaxProfile::create(['company_id' => $company->id, 'name' => 'ضريبة مبيعات', 'tax_type' => 'sales', 'tax_percent' => 16, 'jofotara_tax_code' => 'S', 'is_active' => true]);
        AuditLog::create(['user_id' => $user->id, 'action' => 'master_data.unit.created', 'auditable_type' => Unit::class, 'auditable_id' => $unit->id]);
        $this->withoutMiddleware(\Spatie\Permission\Middleware\PermissionMiddleware::class);
        $this->actingAs($user);

        $this->get(route('company.units.index', $company))->assertOk()->assertSee('الوحدات')->assertSee('استخدم الوحدات لتحديد طريقة قياس المنتجات والخدمات.');
        $this->get(route('company.units.create', $company))->assertOk()->assertSee('حفظ وإضافة أخرى');
        $this->get(route('company.units.edit', [$company, $unit]))->assertOk()->assertSee('حفظ التعديلات');
        $this->get(route('company.units.edit', [$company, $globalUnit]))->assertOk()->assertSee('وحدة عامة');

        $this->get(route('company.tax-profiles.index', $company))->assertOk()->assertSee('إعدادات الضرائب')->assertSee('حدد نسب وأنواع الضرائب المستخدمة في الفواتير.');
        $this->get(route('company.tax-profiles.create', $company))->assertOk()->assertSee('كود جوفوتارا');
        $this->get(route('company.tax-profiles.edit', [$company, $taxProfile]))->assertOk()->assertSee('حفظ التعديلات');

        $this->get(route('company.activity.index', $company))->assertOk()->assertSee('سجل النشاطات')->assertSee('master_data.unit.created');

        foreach (array_merge(glob(resource_path('views/company/master-data/units/*.blade.php')) ?: [], glob(resource_path('views/company/master-data/tax-profiles/*.blade.php')) ?: [], [resource_path('views/company/activity/index.blade.php')]) as $file) {
            $this->assertStringNotContainsString('@vite', file_get_contents($file), $file);
        }
    }

    public function test_units_and_tax_profile_company_isolation_remains_valid(): void
    {
        $companyA = Company::create(['legal_name_ar' => 'شركة أ', 'tax_number' => (string) random_int(100000, 999999)]);
        $companyB = Company::create(['legal_name_ar' => 'شركة ب', 'tax_number' => (string) random_int(100000, 999999)]);
        $unit = Unit::create(['company_id' => $companyA->id, 'code' => 'A'.random_int(100, 999), 'name' => 'Unit A', 'name_ar' => 'وحدة أ', 'is_active' => true]);
        $taxProfile = TaxProfile::create(['company_id' => $companyA->id, 'name' => 'ضريبة أ', 'tax_type' => 'sales', 'tax_percent' => 5, 'is_active' => true]);
        $this->withoutMiddleware(\Spatie\Permission\Middleware\PermissionMiddleware::class);
        $this->actingAs(\App\Models\User::factory()->create(['company_id' => $companyA->id]));

        $this->get(route('company.units.edit', [$companyA, $unit]))->assertOk();
        $this->get(route('company.units.edit', [$companyB, $unit]))->assertNotFound();
        $this->get(route('company.tax-profiles.edit', [$companyA, $taxProfile]))->assertOk();
        $this->get(route('company.tax-profiles.edit', [$companyB, $taxProfile]))->assertNotFound();
    }


    public function test_product_category_pages_render_icon_picker_and_do_not_use_vite(): void
    {
        $company = Company::create(['legal_name_ar' => 'شركة فئات', 'tax_number' => (string) random_int(100000, 999999)]);
        $category = ProductCategory::create(['company_id' => $company->id, 'name_ar' => 'مشروبات', 'name_en' => 'Drinks', 'code' => 'DRINKS', 'icon' => '☕', 'is_active' => true]);
        $this->withoutMiddleware(\Spatie\Permission\Middleware\PermissionMiddleware::class);
        $this->actingAs(\App\Models\User::factory()->create(['company_id' => $company->id]));

        $this->get(route('company.product-categories.index', $company))->assertOk()->assertSee('فئات المنتجات')->assertSee('استخدم الفئات لتنظيم المنتجات والخدمات.');
        $this->get(route('company.product-categories.create', $company))->assertOk()->assertSee('حفظ وإضافة أخرى')->assertSee('🗂️');
        $this->get(route('company.product-categories.edit', [$company, $category]))->assertOk()->assertSee('آخر تحديث')->assertSee('☕');

        foreach (glob(resource_path('views/company/master-data/categories/*.blade.php')) ?: [] as $file) {
            $this->assertStringNotContainsString('@vite', file_get_contents($file), $file);
        }
    }

    public function test_product_category_icon_is_saved_and_empty_arabic_name_is_rejected(): void
    {
        $company = Company::create(['legal_name_ar' => 'شركة أيقونات', 'tax_number' => (string) random_int(100000, 999999)]);
        $controller = app(ProductCategoryController::class);

        $controller->store($this->request(['name_ar' => 'حلويات', 'name_en' => 'Desserts', 'code' => 'DESSERTS', 'description' => 'فئة الحلويات', 'icon' => '🍽️', 'is_active' => '1']), $company);
        $this->assertDatabaseHas('product_categories', ['company_id' => $company->id, 'code' => 'DESSERTS', 'icon' => '🍽️']);

        try {
            $controller->store($this->request(['name_ar' => '   ', 'code' => 'EMPTY-NAME', 'icon' => '📦']), $company);
            $this->fail('Empty Arabic category name should fail validation.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('name_ar', $exception->errors());
        }
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
