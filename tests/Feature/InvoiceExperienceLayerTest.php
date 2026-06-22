<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceShare;
use App\Models\InvoiceTemplate;
use App\Services\Invoices\InvoiceNotificationService;
use App\Services\Invoices\InvoicePdfService;
use App\Services\Invoices\InvoiceShareService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InvoiceExperienceLayerTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_company_user_seeded_with_owner_role(): void
    {
        $company = Company::where('tax_number', '9578331')->firstOrFail();
        $user = DB::table('users')->where('email', 'company@invosync.local')->first();
        $this->assertNotNull($user);
        $this->assertSame($company->id, $user->company_id);
        $this->assertSame('active', $user->status);
        $ownerRoleId = DB::table('roles')->where('company_id', $company->id)->where('name', 'Owner')->value('id');
        $this->assertDatabaseHas('model_has_roles', ['role_id' => $ownerRoleId, 'model_id' => $user->id, 'model_type' => 'App\\Models\\User', 'company_id' => $company->id]);
    }

    public function test_templates_and_branding_settings_are_seeded(): void
    {
        $company = Company::where('tax_number', '9578331')->firstOrFail();
        $this->assertDatabaseHas('invoice_templates', ['slug' => 'arabic-classic', 'is_default' => true, 'view_path' => 'company.invoice-templates.render.arabic-classic']);
        foreach (['arabic-classic', 'arabic-modern', 'bilingual-ar-en', 'retail-receipt', 'corporate-tax'] as $slug) {
            $this->assertDatabaseHas('invoice_templates', ['slug' => $slug, 'is_active' => true]);
        }
        $this->assertCount(5, InvoiceTemplate::whereNull('company_id')->get());
        $this->assertDatabaseHas('company_settings', ['company_id' => $company->id, 'category' => 'invoice_branding', 'key' => 'invoice_primary_color']);
        $this->assertDatabaseHas('company_settings', ['company_id' => $company->id, 'category' => 'invoice_branding', 'key' => 'invoice_template_id']);
    }

    public function test_pdf_rendering_uses_template_and_branding(): void
    {
        $invoice = $this->makeInvoice();
        $html = app(InvoicePdfService::class)->html($invoice);
        $this->assertStringContainsString($invoice->invoice_number, $html);
        $this->assertStringContainsString('QR Code will appear after submission to the National E-Invoicing System', $html);
        $this->assertStringContainsString('فاتورة ضريبية', $html);
        $this->assertStringNotContainsString('@vite', $html);
    }


    public function test_company_can_select_default_template_and_preview_qr_states(): void
    {
        $invoice = $this->makeInvoice();
        $company = $invoice->company;
        $template = InvoiceTemplate::where('slug', 'corporate-tax')->firstOrFail();

        \App\Models\CompanySetting::updateOrCreate(['company_id' => $company->id, 'category' => 'invoice_branding', 'key' => 'invoice_template_id'], ['value' => (string) $template->id]);
        $this->assertDatabaseHas('company_settings', ['company_id' => $company->id, 'category' => 'invoice_branding', 'key' => 'invoice_template_id', 'value' => (string) $template->id]);

        $html = app(\App\Services\Invoices\InvoicePdfRenderer::class)->html($invoice, $template);
        $this->assertStringContainsString('QR Code will appear after submission to the National E-Invoicing System', $html);

        $invoice->forceFill(['jofotara_qr' => 'QR-EXACT-VALUE', 'jofotara_uuid' => 'UUID-1'])->save();
        $htmlWithQr = app(\App\Services\Invoices\InvoicePdfRenderer::class)->html($invoice->refresh(), $template);
        $this->assertStringContainsString('QR-EXACT-VALUE', $htmlWithQr);
        $this->assertSame('QR-EXACT-VALUE', $invoice->refresh()->jofotara_qr);
    }

    public function test_no_vite_is_used_in_invoice_templates(): void
    {
        foreach (glob(resource_path('views/company/invoice-templates/**/*.blade.php')) ?: [] as $file) {
            $this->assertStringNotContainsString('@vite', file_get_contents($file), $file);
        }
    }

    public function test_share_token_public_access_and_notification(): void
    {
        $invoice = $this->makeInvoice();
        $share = app(InvoiceShareService::class)->create($invoice, 'link');
        app(InvoiceNotificationService::class)->record($invoice, 'shared');

        $this->assertInstanceOf(InvoiceShare::class, $share);
        $this->assertDatabaseHas('invoice_shares', ['invoice_id' => $invoice->id, 'company_id' => $invoice->company_id, 'channel' => 'link']);
        $this->assertDatabaseHas('notifications', ['type' => 'invoice.shared', 'notifiable_type' => 'App\\Models\\Company', 'notifiable_id' => $invoice->company_id]);

        $this->get(route('invoices.shared.show', $share->token))->assertOk()->assertSee($invoice->invoice_number);
        $this->assertNotNull($share->refresh()->last_accessed_at);
    }

    private function makeInvoice(): Invoice
    {
        $company = Company::where('tax_number', '9578331')->firstOrFail();
        $invoice = Invoice::query()->create([
            'company_id' => $company->id,
            'supplier_id' => $company->id,
            'invoice_number' => 'EXP-TEST-'.uniqid(),
            'uuid' => '00000000-0000-4000-8000-000000000001',
            'icv' => random_int(10000, 99999),
            'invoice_type' => Invoice::TYPE_TAX_INVOICE,
            'invoice_subtype' => 'SALE',
            'invoice_scope' => 'local',
            'payment_type' => 'receivable',
            'taxpayer_type' => 'income',
            'status' => Invoice::STATUS_DRAFT,
            'issue_date' => now()->toDateString(),
            'issue_time' => now()->format('H:i:s'),
            'currency' => 'JOD',
            'currency_code' => 'JOD',
            'exchange_rate' => '1.000000',
            'subtotal' => '10.000000',
            'discount_amount' => '0.000000',
            'discount_total' => '0.000000',
            'taxable_amount' => '10.000000',
            'tax_amount' => '0.000000',
            'tax_total' => '0.000000',
            'total_amount' => '10.000000',
            'payable_amount' => '10.000000',
            'grand_total' => '10.000000',
        ]);
        $invoice->items()->create([
            'description' => 'Line item',
            'quantity' => '1.000000',
            'unit_price' => '10.000000',
            'discount' => '0.000000',
            'discount_amount' => '0.000000',
            'tax_category' => 'Z',
            'tax_percent' => '0.000000',
            'line_extension_amount' => '10.000000',
            'tax_amount' => '0.000000',
            'line_total' => '10.000000',
        ]);

        return $invoice->refresh();
    }
}
