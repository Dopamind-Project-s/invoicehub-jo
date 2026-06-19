<?php

namespace Database\Seeders;

use App\Models\Company;
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
        $items = [
            ['description' => 'وجبة منسف لحم بلدي', 'quantity' => '1.000000000', 'unit_price' => '8.000000000', 'discount' => '0.000000000', 'tax_category' => 'Z', 'tax_percent' => '0.000000000'],
            ['description' => 'ماتريكس', 'quantity' => '8.000000000', 'unit_price' => '0.180000000', 'discount' => '0.000000000', 'tax_category' => 'Z', 'tax_percent' => '0.000000000'],
            ['description' => 'توصيل', 'quantity' => '1.000000000', 'unit_price' => '1.000000000', 'discount' => '0.000000000', 'tax_category' => 'Z', 'tax_percent' => '0.000000000'],
        ];
        $tax = app(TaxCalculationService::class);
        $totals = $tax->calculateInvoice($items);
        $invoice = Invoice::updateOrCreate(
            ['invoice_number' => 'INV_2026_00001'],
            array_merge($totals, [
                'uuid' => '11111111-2222-4333-8444-555555555555',
                'icv' => 1,
                'invoice_type' => 'STANDARD',
                'invoice_subtype' => 'SALE',
                'invoice_scope' => 'local',
                'payment_type' => 'receivable',
                'taxpayer_type' => 'income',
                'issue_date' => now()+1->toDateString(),
                'issue_time' => now()->format('H:i:s'),
                'currency_code' => 'JOD',
                'exchange_rate' => '1.000000',
                'supplier_id' => $company->id,
                'customer_id' => $customer->id,
                'status' => 'DRAFT',
            ])
        );
        $invoice->items()->delete();
        foreach ($items as $item) {
            $line = $tax->calculateLine($item['quantity'], $item['unit_price'], $item['discount'], $item['tax_percent']);
            $invoice->items()->create(array_merge($item, [
                'line_extension_amount' => $line['line_extension_amount'],
                'tax_amount' => $line['tax_amount'],
                'line_total' => $line['line_total'],
            ]));
        }
        $company->forceFill(['last_icv' => max((int) $company->last_icv, 1)])->save();
    }
}
