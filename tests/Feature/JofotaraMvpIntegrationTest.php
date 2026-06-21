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


    public function test_jofotara_submit_action_visibility_rules_are_clear(): void
    {
        [$company, $user] = $this->companyUser();
        $invoice = Invoice::where('company_id', $company->id)->where('status', Invoice::STATUS_DRAFT)->firstOrFail();

        $this->actingAs($user)->get(route('company.invoices.show', [$company, $invoice]))
            ->assertOk()
            ->assertDontSee('إرسال إلى نظام الفوترة الوطني')
            ->assertDontSee('إرسال للاعتماد');

        $invoice->forceFill(['status' => Invoice::STATUS_READY])->save();
        $company->featureKeys()->detach(FeatureKey::where('code', 'JOFOTARA_SUBMIT')->firstOrFail()->id);
        $this->actingAs($user)->get(route('company.invoices.show', [$company, $invoice]))
            ->assertOk()
            ->assertSee('هذه المنشأة لا تملك ميزة الإرسال للفوترة', false)
            ->assertDontSee('إرسال إلى نظام الفوترة الوطني');

        $company->featureKeys()->syncWithoutDetaching([FeatureKey::where('code', 'JOFOTARA_SUBMIT')->firstOrFail()->id]);
        $company->forceFill(['jofotara_client_id' => null, 'jofotara_secret_key' => null])->save();
        $this->actingAs($user)->get(route('company.invoices.show', [$company, $invoice]))
            ->assertOk()
            ->assertSee('بيانات الربط مع نظام الفوترة غير مكتملة', false);
    }

    public function test_ready_invoice_can_be_submitted_to_jofotara_with_mocked_http_response(): void
    {
        config(['services.jofotara.initial_pih' => 'INITIAL-PIH']);
        [$company, $user] = $this->companyUser();
        $company->update(['jofotara_client_id' => 'client', 'jofotara_secret_key' => 'secret-key-value', 'jofotara_source_id' => 'SRC-1']);
        $company->featureKeys()->syncWithoutDetaching([FeatureKey::where('code', 'JOFOTARA_SUBMIT')->firstOrFail()->id]);
        $invoice = Invoice::where('company_id', $company->id)->orderBy('icv')->firstOrFail();
        $invoice->forceFill(['status' => Invoice::STATUS_READY, 'icv' => 1, 'jofotara_status' => null])->save();

        Http::fake(['*' => Http::response(['EINV_INV_UUID' => 'JF-UUID-1', 'EINV_QR' => 'QR-CODE-1', 'EINV_STATUS' => 'SUBMITTED', 'EINV_RESULTS' => ['status' => 'PASS'], 'EINV_MESSAGE' => 'Submitted'], 200)]);

        $this->actingAs($user)->post(route('company.invoices.jofotara.submit', [$company, $invoice]))->assertRedirect();

        $invoice->refresh();
        $this->assertSame('SUBMITTED', $invoice->jofotara_status);
        $this->assertSame('PASS', $invoice->jofotara_validation_result);
        $this->assertSame('JF-UUID-1', $invoice->jofotara_uuid);
        $this->assertSame('QR-CODE-1', $invoice->jofotara_qr);
        $this->assertSame(Invoice::STATUS_SUBMITTED, $invoice->status);
        $this->assertDatabaseHas('invoice_submission_logs', ['invoice_id' => $invoice->id, 'status' => 'SUBMITTED']);
        Http::assertSent(function ($request) {
            $payload = $request->data();

            return $request->hasHeader('Client-Id', 'client')
                && $request->hasHeader('Secret-Key', 'secret-key-value')
                && $request->hasHeader('Content-Type', 'application/json')
                && $request->hasHeader('Accept', '*/*')
                && array_key_exists('invoice', $payload)
                && count($payload) === 1
                && base64_decode($payload['invoice'], true) !== false
                && ! str_contains(json_encode($payload), 'secret-key-value');
        });
    }

    public function test_invoice_details_show_qr_image_and_qr_route_returns_png_when_einv_qr_exists(): void
    {
        [$company, $user] = $this->companyUser();
        $invoice = Invoice::where('company_id', $company->id)->firstOrFail();
        $invoice->forceFill(['jofotara_status' => 'SUBMITTED', 'jofotara_validation_result' => 'PASS', 'jofotara_uuid' => 'UUID-QR', 'jofotara_qr' => 'QR-FROM-EINV'])->save();

        $this->actingAs($user)->get(route('company.invoices.show', [$company, $invoice]))
            ->assertOk()
            ->assertSee('حالة الفاتورة المحلية', false)
            ->assertSee('حالة جوفوتارا', false)
            ->assertSee('نتيجة التحقق', false)
            ->assertSee('رمز QR', false)
            ->assertSee(route('company.invoices.qr', [$company, $invoice]), false);

        $this->actingAs($user)->get(route('company.invoices.qr', [$company, $invoice]))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');
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

    public function test_local_invoices_do_not_affect_first_jofotara_icv(): void
    {
        config(['services.jofotara.initial_pih' => 'INITIAL-PIH']);
        [$company, $user] = $this->companyUser();
        $company->update(['jofotara_client_id' => 'client', 'jofotara_secret_key' => 'secret-key-value', 'jofotara_source_id' => 'SRC-1']);
        $company->featureKeys()->syncWithoutDetaching([FeatureKey::where('code', 'JOFOTARA_SUBMIT')->firstOrFail()->id]);

        Invoice::where('company_id', $company->id)->update(['jofotara_status' => null, 'xml_hash' => null, 'jofotara_uuid' => null]);
        $invoice = Invoice::where('company_id', $company->id)->where('status', Invoice::STATUS_READY)->firstOrFail();
        $invoice->forceFill(['icv' => 99, 'jofotara_status' => null])->save();

        Http::fake(['*' => Http::response(['EINV_INV_UUID' => 'FIRST-UUID', 'EINV_QR' => 'FIRST-QR', 'EINV_STATUS' => 'ACCEPTED'], 200)]);

        $this->actingAs($user)->post(route('company.invoices.jofotara.submit', [$company, $invoice]))->assertRedirect();

        $invoice->refresh();
        $this->assertSame(1, (int) $invoice->icv);
        $this->assertSame('ACCEPTED', $invoice->jofotara_status);
        $this->assertSame(1, (int) $company->refresh()->last_icv);
    }

    public function test_accepted_jofotara_invoice_is_pih_source_and_failed_invoice_is_ignored(): void
    {
        [$company, $user] = $this->companyUser();
        $company->update(['jofotara_client_id' => 'client', 'jofotara_secret_key' => 'secret-key-value', 'jofotara_source_id' => 'SRC-1']);
        $company->featureKeys()->syncWithoutDetaching([FeatureKey::where('code', 'JOFOTARA_SUBMIT')->firstOrFail()->id]);

        Invoice::where('company_id', $company->id)->delete();
        $accepted = Invoice::create(array_merge($this->invoicePayload($company, Invoice::TYPE_TAX_INVOICE, 1), [
            'invoice_number' => 'JF-ACCEPTED-1',
            'icv' => 1,
            'status' => Invoice::STATUS_SUBMITTED,
            'jofotara_status' => 'ACCEPTED',
            'jofotara_uuid' => 'ACCEPTED-UUID-1',
            'xml_hash' => 'HASH-ACCEPTED-1',
            'accepted_at' => now(),
        ]));
        Invoice::create(array_merge($this->invoicePayload($company, Invoice::TYPE_TAX_INVOICE, 2), [
            'invoice_number' => 'JF-FAILED-2',
            'icv' => 2,
            'status' => Invoice::STATUS_READY,
            'jofotara_status' => 'ERROR',
            'xml_hash' => 'HASH-FAILED-2',
            'jofotara_uuid' => 'FAILED-UUID-2',
        ]));
        $next = Invoice::create(array_merge($this->invoicePayload($company, Invoice::TYPE_TAX_INVOICE, 3), [
            'invoice_number' => 'JF-NEXT',
            'icv' => 77,
            'status' => Invoice::STATUS_READY,
            'jofotara_status' => null,
        ]));

        Http::fake(['*' => Http::response(['EINV_INV_UUID' => 'SECOND-UUID', 'EINV_QR' => 'SECOND-QR', 'EINV_STATUS' => 'ACCEPTED'], 200)]);

        $this->actingAs($user)->post(route('company.invoices.jofotara.submit', [$company, $next]))->assertRedirect();

        $next->refresh();
        $this->assertSame(2, (int) $next->icv);
        $this->assertSame($accepted->xml_hash, $next->previous_invoice_hash);
        $this->assertSame('ACCEPTED', $next->jofotara_status);
    }

    public function test_jofotara_diagnostic_panel_and_uat_page_are_visible_outside_production(): void
    {
        [$company, $user] = $this->companyUser();
        $invoice = Invoice::where('company_id', $company->id)->where('status', Invoice::STATUS_READY)->firstOrFail();

        $this->actingAs($user)->get(route('company.invoices.show', [$company, $invoice]))
            ->assertOk()
            ->assertSee('تشخيص سلسلة جوفوتارا', false)
            ->assertSee('هذه أول فاتورة يتم إرسالها إلى نظام الفوترة الوطني', false);

        $this->actingAs($user)->get(route('company.invoices.jofotara.uat', $company))
            ->assertOk()
            ->assertSee('JoFotara UAT Status')
            ->assertSee('حالة السلسلة', false);
    }

    public function test_jofotara_uat_page_is_unavailable_in_production(): void
    {
        [$company, $user] = $this->companyUser();
        app()->detectEnvironment(fn () => 'production');

        $this->actingAs($user)->get(route('company.invoices.jofotara.uat', $company))->assertNotFound();
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
