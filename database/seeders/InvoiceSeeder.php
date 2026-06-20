<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\Jofotara\TaxCalculationService;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('tax_number', '9578331')->firstOrFail();
        $customer = Customer::where('name', 'عميل نقدي')->firstOrFail();
        $contact = Contact::where('company_id', $company->id)->where('name_ar', 'عميل نقدي')->first();
        $items = [
            ['description' => 'وجبة منسف لحم بلدي', 'quantity' => '1.000000000', 'unit_price' => '8.000000000', 'discount' => '0.000000000', 'tax_category' => 'Z', 'tax_percent' => '0.000000000'],
            ['description' => 'ماتريكس', 'quantity' => '8.000000000', 'unit_price' => '0.180000000', 'discount' => '0.000000000', 'tax_category' => 'Z', 'tax_percent' => '0.000000000'],
            ['description' => 'توصيل', 'quantity' => '1.000000000', 'unit_price' => '1.000000000', 'discount' => '0.000000000', 'tax_category' => 'Z', 'tax_percent' => '0.000000000'],
        ];
        $this->seedInvoice('INV_2026_00001', Invoice::STATUS_DRAFT, 1, $company, $customer->id, $contact?->id, $items);
        $this->seedInvoice('INV_2026_00002', Invoice::STATUS_APPROVED, 2, $company, $customer->id, $contact?->id, $items);
        $company->forceFill(['last_icv' => max((int) $company->last_icv, 2)])->save();
    }

    private function seedInvoice(string $number, string $status, int $icv, Company $company, int $customerId, ?int $contactId, array $items): void
    {
        $tax = app(TaxCalculationService::class);
        $totals = $tax->calculateInvoice($items);
        $invoice = Invoice::updateOrCreate(
            ['invoice_number' => $number],
            array_merge($totals, [
                'company_id' => $company->id,
                'contact_id' => $contactId,
                'uuid' => '11111111-2222-4333-8444-'.str_pad((string) $icv, 12, '0', STR_PAD_LEFT),
                'icv' => $icv,
                'invoice_type' => Invoice::TYPE_TAX_INVOICE,
                'invoice_subtype' => 'SALE',
                'invoice_scope' => 'local',
                'payment_type' => 'receivable',
                'taxpayer_type' => 'income',
                'issue_date' => now()->addDays($icv)->toDateString(),
                'issue_time' => now()->format('H:i:s'),
                'due_date' => now()->addDays(30 + $icv)->toDateString(),
                'currency' => 'JOD',
                'currency_code' => 'JOD',
                'exchange_rate' => '1.000000',
                'supplier_id' => $company->id,
                'customer_id' => $customerId,
                'status' => $status,
                'tax_total' => $totals['tax_amount'],
                'discount_total' => $totals['discount_amount'],
                'grand_total' => $totals['payable_amount'],
                'approved_at' => $status === Invoice::STATUS_APPROVED ? now() : null,
            ])
        );
        $invoice->items()->delete();
        foreach ($items as $item) {
            $line = $tax->calculateLine($item['quantity'], $item['unit_price'], $item['discount'], $item['tax_percent']);
            $invoice->items()->create(array_merge($item, [
                'discount_amount' => $item['discount'],
                'line_extension_amount' => $line['line_extension_amount'],
                'tax_amount' => $line['tax_amount'],
                'line_total' => $line['line_total'],
            ]));
        }
    }
}
