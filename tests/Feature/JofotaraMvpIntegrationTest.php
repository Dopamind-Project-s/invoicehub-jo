<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Contact;
use App\Models\FeatureKey;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class JofotaraMvpIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_invoice_create_route_and_template_preview_work_for_company_user(): void
    {
        [$company, $user] = $this->companyUser();

        $this->actingAs($user)->get(route('company.invoices.index', $company))->assertOk();
        $this->actingAs($user)->get(route('company.invoices.create', $company))->assertOk()->assertSee('حفظ');

        foreach (\App\Models\InvoiceTemplate::query()->whereNull('company_id')->get() as $template) {
            $this->actingAs($user)->get(route('company.invoice-templates.preview', [$company, $template]))->assertOk();
        }
    }


    public function test_internal_and_legacy_invoice_types_can_be_stored(): void
    {
        [$company] = $this->companyUser();

        foreach (array_merge(Invoice::INTERNAL_TYPES, Invoice::LEGACY_JOFOTARA_TYPES) as $index => $type) {
            Invoice::create($this->invoicePayload($company, $type, $index));
        }

        foreach (array_merge(Invoice::INTERNAL_TYPES, Invoice::LEGACY_JOFOTARA_TYPES) as $type) {
            $this->assertDatabaseHas('invoices', ['company_id' => $company->id, 'invoice_type' => $type]);
        }
    }

    public function test_company_user_can_create_invoice_through_ui_with_internal_type(): void
    {
        [$company, $user] = $this->companyUser();
        $contact = Contact::where('company_id', $company->id)->firstOrFail();
        $product = Product::where('company_id', $company->id)->firstOrFail();

        $response = $this->actingAs($user)->post(route('company.invoices.store', $company), [
            'contact_id' => $contact->id,
            'invoice_type' => Invoice::TYPE_TAX_INVOICE,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'currency' => 'JOD',
            'items' => [[
                'product_id' => $product->id,
                'description' => 'Hotfix test line',
                'quantity' => 1,
                'unit_price' => 10,
                'discount_amount' => 0,
                'tax_percent' => 0,
            ]],
        ]);

        $invoice = Invoice::where('company_id', $company->id)->where('invoice_type', Invoice::TYPE_TAX_INVOICE)->latest()->firstOrFail();
        $response->assertRedirect(route('company.invoices.show', [$company, $invoice]));
        $this->assertSame(Invoice::TYPE_TAX_INVOICE, $invoice->invoice_type);
    }

    public function test_plan_change_replaces_old_plan_features_and_adds_new_plan_features(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $company = Company::where('tax_number', '9578331')->firstOrFail();
        $starter = Plan::where('slug', 'starter')->firstOrFail();
        $professional = Plan::where('slug', 'professional')->firstOrFail();

        $company->subscriptions()->where('status', 'active')->update(['plan_id' => $starter->id]);
        $company->featureKeys()->sync($starter->featureKeys()->pluck('feature_keys.id')->all());

        $manual = FeatureKey::where('code', 'API_ACCESS')->firstOrFail();
        $response = $this->actingAs($admin)->put(route('admin.companies.update', $company), [
            'name_ar' => $company->name_ar ?: $company->legal_name_ar,
            'name_en' => $company->name_en,
            'tax_number' => $company->tax_number,
            'national_number' => $company->national_number,
            'phone' => $company->phone,
            'email' => $company->email,
            'status' => 'active',
            'jofotara_source_id' => $company->jofotara_source_id,
            'default_language' => $company->default_language ?: 'ar',
            'default_currency' => $company->default_currency ?: 'JOD',
            'plan_id' => $professional->id,
            'feature_keys' => [$manual->id],
        ]);

        $response->assertRedirect(route('admin.companies.show', $company));
        $codes = $company->refresh()->featureKeys()->pluck('code')->all();
        foreach ($professional->featureKeys()->pluck('code')->all() as $code) {
            $this->assertContains($code, $codes);
        }
        $this->assertContains('API_ACCESS', $codes);
        $this->assertDatabaseHas('subscriptions', ['company_id' => $company->id, 'plan_id' => $professional->id, 'status' => 'active']);
    }

    public function test_approved_invoice_can_be_submitted_to_jofotara_with_mocked_http_response(): void
    {
        config(['services.jofotara.initial_pih' => 'INITIAL-PIH']);
        [$company, $user] = $this->companyUser();
        $company->update(['jofotara_client_id' => 'client', 'jofotara_secret_key' => 'secret-key-value', 'jofotara_source_id' => 'SRC-1']);
        $company->featureKeys()->syncWithoutDetaching([FeatureKey::where('code', 'JOFOTARA_SUBMIT')->firstOrFail()->id]);
        $invoice = Invoice::where('company_id', $company->id)->orderBy('icv')->firstOrFail();
        $invoice->forceFill(['status' => Invoice::STATUS_APPROVED, 'icv' => 1, 'jofotara_status' => null])->save();

        Http::fake(['*' => Http::response(['EINV_NUM' => 'JF-UUID-1', 'EINV_QR' => 'QR-CODE-1', 'EINV_STATUS' => 'ACCEPTED'], 200)]);

        $this->actingAs($user)->post(route('company.invoices.jofotara.submit', [$company, $invoice]))->assertRedirect();

        $invoice->refresh();
        $this->assertSame('ACCEPTED', $invoice->jofotara_status);
        $this->assertSame('JF-UUID-1', $invoice->jofotara_uuid);
        $this->assertSame('QR-CODE-1', $invoice->jofotara_qr);
        $this->assertSame(Invoice::STATUS_APPROVED, $invoice->status);
        $this->assertDatabaseHas('invoice_submission_logs', ['invoice_id' => $invoice->id, 'status' => 'ACCEPTED']);
    }

    public function test_jofotara_import_prevents_duplicate_external_invoices(): void
    {
        [$company, $user] = $this->companyUser();
        $company->featureKeys()->syncWithoutDetaching([FeatureKey::where('code', 'JOFOTARA_SYNC')->firstOrFail()->id]);
        $payload = [[
            'invoice_number' => 'EXT-100',
            'issue_date' => '2026-06-01',
            'total' => '25.000000',
            'currency' => 'JOD',
            'jofotara_uuid' => 'EXT-UUID-100',
            'jofotara_status' => 'ACCEPTED',
            'jofotara_qr' => 'QR-EXT',
        ]];

        $file = UploadedFile::fake()->createWithContent('imports.json', json_encode($payload, JSON_UNESCAPED_UNICODE));
        $this->actingAs($user)->post(route('company.invoices.import.store', $company), ['import_file' => $file])->assertRedirect();
        $fileAgain = UploadedFile::fake()->createWithContent('imports.json', json_encode($payload, JSON_UNESCAPED_UNICODE));
        $this->actingAs($user)->post(route('company.invoices.import.store', $company), ['import_file' => $fileAgain])->assertRedirect();

        $this->assertSame(1, Invoice::where('company_id', $company->id)->where('jofotara_uuid', 'EXT-UUID-100')->count());
        $this->assertDatabaseHas('invoices', ['invoice_number' => 'EXT-100', 'source' => 'jofotara_import', 'jofotara_status' => 'ACCEPTED']);
    }


    /** @return array<string,mixed> */
    private function invoicePayload(Company $company, string $type, int $index): array
    {
        return [
            'company_id' => $company->id,
            'supplier_id' => $company->id,
            'invoice_number' => 'TYPE-'.str_replace('_', '-', $type).'-'.$index,
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'icv' => 1000 + $index,
            'invoice_type' => $type,
            'invoice_subtype' => str_starts_with($type, 'credit') || $type === Invoice::TYPE_CREDIT_NOTE ? 'CREDIT_NOTE' : 'SALE',
            'invoice_scope' => 'local',
            'payment_type' => 'receivable',
            'taxpayer_type' => 'income',
            'status' => Invoice::STATUS_DRAFT,
            'issue_date' => now()->toDateString(),
            'issue_time' => now()->format('H:i:s'),
            'currency' => 'JOD',
            'currency_code' => 'JOD',
            'exchange_rate' => '1.000000',
            'subtotal' => '1.000000',
            'discount_amount' => '0.000000',
            'discount_total' => '0.000000',
            'taxable_amount' => '1.000000',
            'tax_amount' => '0.000000',
            'tax_total' => '0.000000',
            'total_amount' => '1.000000',
            'payable_amount' => '1.000000',
            'grand_total' => '1.000000',
        ];
    }

    /** @return array{0: Company, 1: User} */
    private function companyUser(): array
    {
        $company = Company::where('tax_number', '9578331')->firstOrFail();
        $user = User::where('email', 'company@invosync.local')->firstOrFail();

        return [$company, $user];
    }
}
