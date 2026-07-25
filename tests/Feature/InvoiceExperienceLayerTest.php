<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\InvoiceShare;
use App\Models\InvoiceTemplate;
use App\Models\User;
use App\Services\Invoices\InvoiceDisplayDataFactory;
use App\Services\Invoices\InvoiceNotificationService;
use App\Services\Invoices\InvoicePdfRenderer;
use App\Services\Invoices\InvoicePdfService;
use App\Services\Invoices\InvoiceShareService;
use App\Services\Invoices\InvoiceTemplateResolver;
use App\Services\Jofotara\QRCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Stringable;
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
        foreach (['arabic-classic', 'arabic-modern', 'bilingual-ar-en', 'retail-receipt', 'corporate-tax', 'jordan-tax-pro', 'premium-ledger', 'minimal-blue'] as $slug) {
            $this->assertDatabaseHas('invoice_templates', ['slug' => $slug, 'is_active' => true]);
        }
        $this->assertCount(8, InvoiceTemplate::whereNull('company_id')->get());
        $this->assertDatabaseHas('company_settings', ['company_id' => $company->id, 'category' => 'invoice_branding', 'key' => 'invoice_primary_color']);
        $this->assertDatabaseHas('company_settings', ['company_id' => $company->id, 'category' => 'invoice_branding', 'key' => 'invoice_template_id']);
    }

    public function test_pdf_rendering_uses_template_and_branding(): void
    {
        $invoice = $this->makeInvoice();
        $html = app(InvoicePdfService::class)->html($invoice);
        $this->assertStringContainsString($invoice->invoice_number, $html);
        $this->assertStringContainsString('رمز QR الرسمي غير متوفر لأن الفاتورة لم تُعتمد بعد من نظام الفوترة الوطني', $html);
        $this->assertStringContainsString('فاتورة ضريبية', $html);
        $this->assertStringNotContainsString('@vite', $html);
    }

    public function test_normal_and_printable_previews_share_content_without_print_layout_regression(): void
    {
        $invoice = $this->makeInvoice();
        $company = $invoice->company;
        $user = User::where('email', 'company@invosync.local')->firstOrFail();

        $normal = $this->actingAs($user)->get(route('company.invoices.show', [$company, $invoice]));
        $normal->assertOk()
            ->assertSee('class="invoice-page invoice-document"', false)
            ->assertDontSee('class="print-preview-shell"', false);

        $printable = $this->actingAs($user)->get(route('company.invoices.printable', [$company, $invoice, 'preview' => 1]));
        $printable->assertOk()
            ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
            ->assertSee('class="print-preview-shell"', false)
            ->assertSee('class="invoice-print-page"', false)
            ->assertSee('class="invoice-page invoice-document"', false)
            ->assertSee('class="invoice-items invoice-items-table"', false)
            ->assertSee('فاتورة ضريبية')
            ->assertSee('بيانات الفاتورة')
            ->assertSee('بيانات العميل')
            ->assertSee('الإجمالي قبل الخصم')
            ->assertSee('1.000')
            ->assertSee('10.000 JOD')
            ->assertDontSee('ةيبيرض ةروتاف')
            ->assertDontSee('ةروتافلا تانايب')
            ->assertDontSee('ليمعلا تانايب')
            ->assertDontSee('file://');
    }

    public function test_every_invoice_template_uses_the_shared_print_contract(): void
    {
        $invoice = $this->makeInvoice();
        $resolver = app(InvoiceTemplateResolver::class);
        $signatures = [];

        foreach (InvoiceTemplate::query()->where('is_active', true)->get() as $template) {
            $html = app(InvoicePdfRenderer::class)->html($invoice, $template);
            $presentation = $resolver->presentation($template);

            $this->assertStringContainsString('class="print-preview-shell"', $html, $template->slug);
            $this->assertStringContainsString('class="invoice-print-page"', $html, $template->slug);
            $this->assertStringContainsString('class="invoice-page invoice-document"', $html, $template->slug);
            $this->assertStringContainsString('class="invoice-items invoice-items-table"', $html, $template->slug);
            $this->assertStringContainsString($presentation['root_class'], $html, $template->slug);
            $this->assertStringContainsString('data-layout="'.$presentation['layout'].'"', $html, $template->slug);
            $this->assertStringContainsString(asset($presentation['stylesheet']), $html, $template->slug);
            $this->assertStringContainsString('فاتورة ضريبية', $html, $template->slug);
            $this->assertStringNotContainsString('ةيبيرض ةروتاف', $html, $template->slug);

            $signatures[$template->slug] = implode('|', array_intersect_key($presentation, array_flip(['layout', 'header', 'info', 'table', 'totals', 'qr'])));
        }

        $this->assertCount(8, array_unique($signatures));
    }

    public function test_template_resolver_has_stable_unique_definitions_and_classic_fallback(): void
    {
        $resolver = app(InvoiceTemplateResolver::class);
        $definitions = InvoiceTemplateResolver::definitions();

        $this->assertCount(8, $definitions);
        $this->assertSame($definitions['arabic-classic'], $resolver->presentation('unknown-template'));
        $this->assertCount(8, array_unique(array_column($definitions, 'root_class')));
        $this->assertCount(8, array_unique(array_column($definitions, 'stylesheet')));
        $this->assertCount(8, array_unique(array_map(fn (array $definition): string => implode('|', array_intersect_key($definition, array_flip(['layout', 'header', 'info', 'table', 'totals', 'qr']))), $definitions)));
    }

    public function test_template_management_preview_uses_requested_theme_without_changing_default(): void
    {
        $invoice = $this->makeInvoice();
        $company = $invoice->company;
        $user = User::where('email', 'company@invosync.local')->firstOrFail();
        $modern = InvoiceTemplate::where('slug', 'arabic-modern')->firstOrFail();
        $defaultBefore = CompanySetting::where('company_id', $company->id)->where('key', 'invoice_template_id')->value('value');

        $this->actingAs($user)->get(route('company.invoice-templates.index', $company))
            ->assertOk()
            ->assertSee('تصميم عربي حديث يبرز هوية المنشأة')
            ->assertSee('class="template-preview-frame"', false);

        $this->actingAs($user)->get(route('company.invoice-templates.preview', [$company, $modern]))
            ->assertOk()
            ->assertSee('invoice-template-arabic-modern', false)
            ->assertSee('data-layout="modern-asymmetric"', false);

        $this->assertSame($defaultBefore, CompanySetting::where('company_id', $company->id)->where('key', 'invoice_template_id')->value('value'));
    }

    public function test_company_can_select_default_template_and_preview_qr_states(): void
    {
        $invoice = $this->makeInvoice();
        $company = $invoice->company;
        $company->forceFill(['logo_path' => 'assets/img/JoFotarah-logo.png'])->save();
        $template = InvoiceTemplate::where('slug', 'corporate-tax')->firstOrFail();

        CompanySetting::updateOrCreate(['company_id' => $company->id, 'category' => 'invoice_branding', 'key' => 'invoice_template_id'], ['value' => (string) $template->id]);
        $this->assertDatabaseHas('company_settings', ['company_id' => $company->id, 'category' => 'invoice_branding', 'key' => 'invoice_template_id', 'value' => (string) $template->id]);

        $html = app(InvoicePdfRenderer::class)->html($invoice, $template);
        $this->assertStringContainsString('رمز QR الرسمي غير متوفر لأن الفاتورة لم تُعتمد بعد من نظام الفوترة الوطني', $html);
        $this->assertStringContainsString('شعار المنشأة', $html);
        $this->assertStringNotContainsString('شعار نظام الفوترة الوطني JoFotara', $html);
        $this->assertStringNotContainsString('<span>UUID</span>', $html);

        $invoice->forceFill(['jofotara_status' => 'SUBMITTED', 'jofotara_validation_result' => 'PASS', 'jofotara_qr' => 'QR-EXACT-VALUE', 'jofotara_uuid' => 'UUID-1'])->save();
        $htmlWithQr = app(InvoicePdfRenderer::class)->html($invoice->refresh(), $template);
        $this->assertStringContainsString('data:image/svg+xml;base64', $htmlWithQr);
        $this->assertStringContainsString('شعار نظام الفوترة الوطني JoFotara', $htmlWithQr);
        $this->assertStringContainsString('data:image/png;base64', $htmlWithQr);
        $this->assertStringContainsString('class="invoice-logo-box invoice-logo-box-national"', $htmlWithQr);
        $this->assertStringContainsString('class="invoice-closing avoid-break"', $htmlWithQr);
        $this->assertStringContainsString('class="invoice-qr-block"', $htmlWithQr);
        $this->assertStringContainsString('class="official-jofotara-qr"', $htmlWithQr);
        $this->assertStringContainsString('class="invoice-number"', $htmlWithQr);
        $this->assertStringContainsString('class="invoice-number invoice-line-total"', $htmlWithQr);
        $this->assertStringContainsString('class="invoice-tax-rate"', $htmlWithQr);
        $this->assertStringNotContainsString('تم إنشاء الصورة من قيمة QR الرسمية', $htmlWithQr);
        $this->assertStringNotContainsString('QR-EXACT-VALUE</small>', $htmlWithQr);
        $this->assertSame('QR-EXACT-VALUE', $invoice->refresh()->jofotara_qr);
    }

    public function test_invoice_document_sanitizes_json_array_xml_and_keeps_arabic_mixed_items(): void
    {
        $invoice = $this->makeInvoice();
        $invoice->forceFill(['notes' => json_encode(['source' => 'api', 'note' => 'ملاحظة نصية فقط'], JSON_UNESCAPED_UNICODE)])->save();
        $invoice->items()->create([
            'description' => 'خدمة عربية / English service طويلة للتحقق من كسر النص',
            'quantity' => '2.000000',
            'unit_price' => '5.000000',
            'discount' => '0.000000',
            'discount_amount' => '1.000000',
            'tax_category' => 'S',
            'tax_percent' => '16.000000',
            'line_extension_amount' => '10.000000',
            'tax_amount' => '1.440000',
            'line_total' => '10.440000',
        ]);

        $html = app(InvoicePdfService::class)->html($invoice->refresh());

        $this->assertStringContainsString('ملاحظة نصية فقط', $html);
        $this->assertStringContainsString('خدمة عربية / English service', $html);
        foreach (['{"source"', 'Array', '[object Object]', '<Invoice', '<?xml'] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $html);
        }
    }

    public function test_official_jofotara_qr_is_source_of_truth_and_no_qr_before_success(): void
    {
        $invoice = $this->makeInvoice();
        $company = $invoice->company;
        $user = User::where('email', 'company@invosync.local')->firstOrFail();

        $this->actingAs($user)->get(route('company.invoices.qr', [$company, $invoice]))->assertNotFound();

        $invoice->forceFill([
            'qr_code' => 'LOCAL-LEGACY-QR-MUST-NOT-WIN',
            'jofotara_status' => 'SUBMITTED',
            'jofotara_validation_result' => 'PASS',
            'jofotara_uuid' => 'OFFICIAL-UUID',
            'jofotara_qr' => 'OFFICIAL-JOFOTARA-PAYLOAD',
        ])->save();

        $html = app(InvoicePdfService::class)->html($invoice->refresh());
        $this->assertStringContainsString('data:image/svg+xml;base64', $html);
        $this->assertStringNotContainsString('LOCAL-LEGACY-QR-MUST-NOT-WIN', $html);

        $response = $this->actingAs($user)->get(route('company.invoices.qr', [$company, $invoice]));
        $response->assertOk()->assertHeader('Content-Type', 'image/svg+xml');
        $this->assertStringContainsString('svg', $response->getContent());
    }

    public function test_pdf_endpoint_is_authorized_and_outputs_pdf_for_arabic_invoice(): void
    {
        $invoice = $this->makeInvoice();
        $company = $invoice->company;
        $user = User::where('email', 'company@invosync.local')->firstOrFail();

        $this->get(route('company.invoices.printable', [$company, $invoice]))->assertRedirect();
        $response = $this->actingAs($user)->get(route('company.invoices.printable', [$company, $invoice]));
        $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_legacy_qr_uuid_url_and_hash_do_not_render_without_official_jofotara_success_state(): void
    {
        $invoice = $this->makeInvoice();
        $company = $invoice->company;
        $user = User::where('email', 'company@invosync.local')->firstOrFail();
        $pdf = app(InvoicePdfService::class);

        foreach ([
            ['qr_code' => 'LEGACY-QR-ONLY'],
            ['uuid' => '11111111-1111-4111-8111-111111111111', 'jofotara_qr' => 'LOCAL-UUID-ONLY'],
            ['jofotara_qr' => route('company.invoices.show', [$company, $invoice])],
            ['jofotara_qr' => hash('sha256', $invoice->invoice_number)],
        ] as $attributes) {
            $invoice->forceFill(array_merge([
                'jofotara_status' => null,
                'jofotara_validation_result' => null,
                'jofotara_uuid' => null,
                'jofotara_qr' => null,
                'qr_code' => null,
            ], $attributes))->save();

            $html = $pdf->html($invoice->refresh());
            $this->assertStringContainsString('رمز QR الرسمي غير متوفر', $html);
            $this->assertStringNotContainsString('data:image/', $html);
            $this->actingAs($user)->get(route('company.invoices.qr', [$company, $invoice]))->assertNotFound();
        }

        $officialPayload = " OFFICIAL-JOFOTARA-PAYLOAD\nبالعربية ";
        $invoice->forceFill([
            'jofotara_status' => 'SUBMITTED',
            'jofotara_validation_result' => 'PASS',
            'jofotara_uuid' => null,
            'jofotara_qr' => $officialPayload,
            'qr_code' => 'LEGACY-QR-MUST-NOT-WIN',
        ])->save();

        $this->assertSame($officialPayload, app(QRCodeService::class)->officialValue($invoice->refresh()));
        $html = $pdf->html($invoice->refresh());
        $this->assertStringContainsString('data:image/svg+xml;base64', $html);
        $this->assertStringNotContainsString('LEGACY-QR-MUST-NOT-WIN', $html);
    }

    public function test_invoice_notes_preserve_valid_text_and_extract_only_business_text_from_structured_values(): void
    {
        $invoice = $this->makeInvoice();
        $factory = app(InvoiceDisplayDataFactory::class);

        foreach ([
            'ملاحظة عربية صالحة',
            'English commercial note',
            'يرجى مراجعة {العقد} قبل الدفع',
            '{not-json-but-valid-business-note',
            '[not-json-but-valid-business-note',
        ] as $note) {
            $invoice->setAttribute('notes', $note);
            $this->assertSame($note, $factory->make($invoice)['invoice']['notes']);
        }

        foreach ([
            json_encode(['source' => 'api', 'note' => 'ملاحظة من note'], JSON_UNESCAPED_UNICODE) => 'ملاحظة من note',
            json_encode(['source' => 'api', 'notes' => 'ملاحظة من notes'], JSON_UNESCAPED_UNICODE) => 'ملاحظة من notes',
            json_encode(['source' => 'api', 'text' => 'ملاحظة من text'], JSON_UNESCAPED_UNICODE) => 'ملاحظة من text',
            json_encode(['metadata' => ['nested' => ['message' => 'ملاحظة متداخلة']]], JSON_UNESCAPED_UNICODE) => 'ملاحظة متداخلة',
        ] as $structured => $expected) {
            $invoice->setAttribute('notes', $structured);
            $this->assertSame($expected, $factory->make($invoice)['invoice']['notes']);
        }

        $invoice->setAttribute('notes', collect(['note' => 'ملاحظة من Collection']));
        $this->assertSame('ملاحظة من Collection', $factory->make($invoice)['invoice']['notes']);

        $invoice->setAttribute('notes', new class implements Stringable
        {
            public function __toString(): string
            {
                return 'Stringable business note';
            }
        });
        $this->assertSame('Stringable business note', $factory->make($invoice)['invoice']['notes']);

        $invoice->setAttribute('notes', ['source' => 'api']);
        $this->assertNull($factory->make($invoice)['invoice']['notes']);

        foreach (['Array', '[object Object]', '<Invoice><ID>1</ID></Invoice>', '<?xml version="1.0"?><Invoice/>'] as $forbidden) {
            $invoice->setAttribute('notes', $forbidden);
            $this->assertNull($factory->make($invoice)['invoice']['notes']);
        }
    }

    public function test_real_arabic_invoice_html_and_pdf_endpoint_include_business_content_and_official_qr(): void
    {
        $invoice = $this->makeInvoice();
        $company = $invoice->company;
        $company->forceFill([
            'name_ar' => 'شركة المستقبل للتقنيات والحلول الرقمية',
            'legal_name_ar' => 'شركة المستقبل للتقنيات والحلول الرقمية',
            'city' => 'عمّان',
            'street' => 'شارع الملك عبدالله',
        ])->save();
        $contact = Contact::query()->create([
            'company_id' => $company->id,
            'type' => Contact::TYPE_CUSTOMER,
            'name_ar' => 'مؤسسة أحمد الزعبي للتجارة العامة',
            'tax_number' => '123456789',
            'phone' => '0790000000',
            'address' => 'الجبيهة',
            'city' => 'عمّان',
            'country' => 'JO',
            'is_active' => true,
        ]);
        $invoice->items()->delete();
        foreach (['خدمة تطوير نظام إلكتروني', 'اشتراك شهري لمنصة InvoSync', 'Technical Support خدمة دعم فني'] as $description) {
            $invoice->items()->create([
                'description' => $description,
                'quantity' => '1.000000',
                'unit_price' => '10.000000',
                'discount' => '0.000000',
                'discount_amount' => '0.000000',
                'tax_category' => 'S',
                'tax_percent' => '16.000000',
                'line_extension_amount' => '10.000000',
                'tax_amount' => '1.600000',
                'line_total' => '11.600000',
            ]);
        }
        $invoice->forceFill([
            'contact_id' => $contact->id,
            'notes' => 'شكرًا لتعاملكم معنا، يرجى الاحتفاظ بالفاتورة.',
            'subtotal' => '30.000000',
            'taxable_amount' => '30.000000',
            'tax_amount' => '4.800000',
            'tax_total' => '4.800000',
            'payable_amount' => '34.800000',
            'grand_total' => '34.800000',
            'jofotara_status' => 'SUBMITTED',
            'jofotara_validation_result' => 'PASS',
            'jofotara_qr' => 'OFFICIAL-ARABIC-PDF-PAYLOAD',
        ])->save();

        $html = app(InvoicePdfService::class)->html($invoice->refresh());
        foreach ([
            'شركة المستقبل للتقنيات والحلول الرقمية',
            'مؤسسة أحمد الزعبي للتجارة العامة',
            'خدمة تطوير نظام إلكتروني',
            'اشتراك شهري لمنصة InvoSync',
            'Technical Support خدمة دعم فني',
            'شكرًا لتعاملكم معنا، يرجى الاحتفاظ بالفاتورة.',
            'data:image/svg+xml;base64',
        ] as $expected) {
            $this->assertStringContainsString($expected, $html);
        }
        foreach (['{"', 'Array', '[object Object]', '<Invoice', '<?xml'] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $html);
        }

        $user = User::where('email', 'company@invosync.local')->firstOrFail();
        $response = $this->actingAs($user)->get(route('company.invoices.printable', [$company, $invoice]));
        $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_company_invoice_routes_enforce_tenant_isolation(): void
    {
        $company = Company::where('tax_number', '9578331')->firstOrFail();
        $user = User::where('email', 'company@invosync.local')->firstOrFail();
        $otherCompany = Company::query()->create([
            'name_ar' => 'شركة أخرى',
            'legal_name_ar' => 'شركة أخرى',
            'tax_number' => '99887766',
            'country_code' => 'JO',
            'status' => 'active',
            'is_active' => true,
        ]);
        $otherInvoice = $this->makeInvoice();
        $otherInvoice->forceFill(['company_id' => $otherCompany->id, 'supplier_id' => $otherCompany->id])->save();

        $this->actingAs($user)->get(route('company.invoices.show', [$company, $otherInvoice]))->assertNotFound();
        $this->actingAs($user)->get(route('company.invoices.printable', [$company, $otherInvoice]))->assertNotFound();
        $this->actingAs($user)->get(route('company.invoices.qr', [$company, $otherInvoice]))->assertNotFound();
    }

    public function test_seeded_schema_contains_plan_multilingual_columns_required_by_fresh_seed(): void
    {
        $this->assertTrue(Schema::hasColumns('plans', ['name', 'name_ar', 'name_en', 'slug']));
        $this->assertDatabaseHas('plans', ['slug' => 'starter', 'name_ar' => 'باقة البداية']);
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
