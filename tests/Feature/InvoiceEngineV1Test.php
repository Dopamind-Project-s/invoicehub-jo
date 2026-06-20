<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\CompanyWorkspace\InvoiceEngineController;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\TaxCategory;
use App\Models\Unit;
use App\Services\Invoices\InvoiceCalculator;
use App\Services\Invoices\InvoicePdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class InvoiceEngineV1Test extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_calculator_stores_subtotal_discount_tax_and_grand_total(): void
    {
        $totals = app(InvoiceCalculator::class)->calculate([
            ['description' => 'Line A', 'quantity' => 2, 'unit_price' => 10, 'discount_amount' => 2, 'tax_percent' => 16],
            ['description' => 'Line B', 'quantity' => 1, 'unit_price' => 5, 'discount_amount' => 0, 'tax_percent' => 0],
        ]);

        $this->assertSame('25.000000', $totals['subtotal']);
        $this->assertSame('2.000000', $totals['discount_total']);
        $this->assertSame('2.880000', $totals['tax_total']);
        $this->assertSame('25.880000', $totals['grand_total']);
        $this->assertSame('20.880000', $totals['items'][0]['line_total']);
    }

    public function test_invoice_creation_persists_calculated_item_values_and_audit_log(): void
    {
        [$company, $contact, $product] = $this->foundation();
        $invoice = app(InvoiceEngineController::class)->store($this->request($contact, $product), $company)->getSession()->get('status');

        $created = Invoice::where('company_id', $company->id)->firstOrFail();
        $this->assertSame(Invoice::STATUS_DRAFT, $created->status);
        $this->assertSame('100.000000', $created->subtotal);
        $this->assertSame('5.000000', $created->discount_total);
        $this->assertSame('15.200000', $created->tax_total);
        $this->assertSame('110.200000', $created->grand_total);
        $this->assertDatabaseHas('invoice_items', ['invoice_id' => $created->id, 'discount_amount' => '5', 'tax_amount' => '15.2', 'line_total' => '110.2']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'invoice.created', 'auditable_id' => $created->id]);
        $this->assertSame('تم إنشاء مسودة الفاتورة.', $invoice);
    }

    public function test_workflow_transitions_and_approval_read_only_rule(): void
    {
        [$company, $contact, $product] = $this->foundation();
        app(InvoiceEngineController::class)->store($this->request($contact, $product), $company);
        $invoice = Invoice::firstOrFail();
        $controller = app(InvoiceEngineController::class);

        $controller->submit(Request::create('/submit', 'POST'), $company, $invoice);
        $this->assertSame(Invoice::STATUS_PENDING, $invoice->refresh()->status);

        $controller->approve(Request::create('/approve', 'POST'), $company, $invoice);
        $this->assertSame(Invoice::STATUS_APPROVED, $invoice->refresh()->status);
        $this->assertNotNull($invoice->approved_at);
        $this->assertTrue($invoice->isReadOnly());
        $this->assertDatabaseHas('audit_logs', ['action' => 'invoice.submitted']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'invoice.approved']);

        $this->expectException(HttpException::class);
        $controller->edit($company, $invoice);
    }

    public function test_pending_invoice_can_be_cancelled_and_company_isolation_is_enforced(): void
    {
        [$company, $contact, $product] = $this->foundation();
        $other = Company::create(['legal_name_ar' => 'شركة أخرى', 'tax_number' => '778899']);
        app(InvoiceEngineController::class)->store($this->request($contact, $product), $company);
        $invoice = Invoice::firstOrFail();
        $controller = app(InvoiceEngineController::class);

        $controller->submit(Request::create('/submit', 'POST'), $company, $invoice);
        $controller->cancel(Request::create('/cancel', 'POST'), $company, $invoice);
        $this->assertSame(Invoice::STATUS_CANCELLED, $invoice->refresh()->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'invoice.cancelled']);

        $this->expectException(HttpException::class);
        $controller->show($other, $invoice);
    }

    public function test_printable_pdf_foundation_returns_a_response_without_jofotara_payloads(): void
    {
        [$company, $contact, $product] = $this->foundation();
        app(InvoiceEngineController::class)->store($this->request($contact, $product), $company);
        $invoice = Invoice::firstOrFail();

        $response = app(InvoicePdfService::class)->download($invoice);

        $this->assertTrue(method_exists($response, 'getStatusCode'));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertDatabaseCount('invoice_xml_logs', 0);
        $this->assertDatabaseCount('invoice_submission_logs', 0);
    }

    /** @return array{0:Company,1:Contact,2:Product} */
    private function foundation(): array
    {
        $company = Company::create(['legal_name_ar' => 'شركة الفواتير', 'tax_number' => fake()->unique()->numerify('######'), 'default_currency' => 'JOD']);
        $contact = Contact::create(['company_id' => $company->id, 'type' => Contact::TYPE_CUSTOMER, 'name_ar' => 'عميل فاتورة', 'country' => 'JO', 'is_active' => true]);
        $unit = Unit::create(['company_id' => $company->id, 'code' => fake()->unique()->bothify('U###'), 'name' => 'قطعة', 'name_ar' => 'قطعة', 'is_active' => true]);
        $taxCategory = TaxCategory::firstOrCreate(['code' => 'S'], ['tax_rate' => 16, 'tax_code' => 'S', 'description' => 'Sales']);
        $product = Product::create(['company_id' => $company->id, 'unit_id' => $unit->id, 'tax_category_id' => $taxCategory->id, 'type' => Product::TYPE_PRODUCT, 'sku' => fake()->unique()->bothify('P###'), 'item_code' => fake()->unique()->bothify('P###'), 'name_ar' => 'منتج فاتورة', 'price' => 100, 'default_price' => 100, 'is_active' => true]);

        return [$company, $contact, $product];
    }

    private function request(Contact $contact, Product $product): Request
    {
        return Request::create('/invoice-test', 'POST', [
            'contact_id' => $contact->id,
            'invoice_type' => Invoice::TYPE_TAX_INVOICE,
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(15)->format('Y-m-d'),
            'currency' => 'JOD',
            'notes' => 'اختبار محرك الفواتير',
            'items' => [[
                'product_id' => $product->id,
                'description' => 'منتج فاتورة',
                'quantity' => 1,
                'unit_price' => 100,
                'discount_amount' => 5,
                'tax_percent' => 16,
            ]],
        ]);
    }
}
