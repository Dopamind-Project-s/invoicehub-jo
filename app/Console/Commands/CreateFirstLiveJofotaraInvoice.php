<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\Jofotara\InvoiceTypeCodeService;
use App\Services\Jofotara\TaxCalculationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateFirstLiveJofotaraInvoice extends Command
{
    protected $signature = 'jofotara:create-first-live-invoice';

    protected $description = 'Create a clean first live JoFotara invoice with ICV 1 and income receivable local classification.';

    public function handle(TaxCalculationService $tax, InvoiceTypeCodeService $typeCodes): int
    {
        $invoice = DB::transaction(function () use ($tax): Invoice {
            $company = Company::where('tax_number', '9578331')->firstOrFail();
            $customer = Customer::updateOrCreate(
                ['name' => 'عميل نقدي'],
                ['customer_type' => 'INDIVIDUAL', 'tax_number' => null, 'national_number' => null, 'country_code' => 'JO', 'city' => 'Jordan', 'address' => 'Jordan', 'postal_code' => '-', 'phone' => '-', 'is_taxable' => true]
            );
            Invoice::query()->where('supplier_id', $company->id)->whereIn('status', ['DRAFT', 'ERROR', 'REJECTED'])->delete();
            if (Invoice::query()->count() === 0) {
                $this->resetInvoiceAutoIncrement();
            }
            $company->forceFill(['last_icv' => 1])->save();
            $items = [
                ['description' => 'وجبة منسف لحم بلدي', 'quantity' => '1.000000000', 'unit_price' => '8.000000000', 'discount' => '0.000000000', 'tax_category' => 'Z', 'tax_percent' => '0.000000000'],
                ['description' => 'ماتريكس', 'quantity' => '8.000000000', 'unit_price' => '0.180000000', 'discount' => '0.000000000', 'tax_category' => 'Z', 'tax_percent' => '0.000000000'],
                ['description' => 'توصيل', 'quantity' => '1.000000000', 'unit_price' => '1.000000000', 'discount' => '0.000000000', 'tax_category' => 'Z', 'tax_percent' => '0.000000000'],
            ];
            $invoice = Invoice::create(array_merge($tax->calculateInvoice($items), [
                'uuid' => (string) Str::uuid(),
                'invoice_number' => 'INV_'.now()->year.'_00001',
                'icv' => 1,
                'invoice_type' => 'STANDARD',
                'invoice_subtype' => 'SALE',
                'invoice_scope' => 'local',
                'payment_type' => 'receivable',
                'taxpayer_type' => 'income',
                'issue_date' => now()->toDateString(),
                'issue_time' => now()->format('H:i:s'),
                'currency_code' => 'JOD',
                'exchange_rate' => '1.000000',
                'supplier_id' => $company->id,
                'customer_id' => $customer->id,
                'status' => 'DRAFT',
            ]));
            foreach ($items as $item) {
                $line = $tax->calculateLine($item['quantity'], $item['unit_price'], $item['discount'], $item['tax_percent']);
                $invoice->items()->create(array_merge($item, [
                    'line_extension_amount' => $line['line_extension_amount'],
                    'tax_amount' => $line['tax_amount'],
                    'line_total' => $line['line_total'],
                ]));
            }

            return $invoice;
        });

        $this->line('invoice id: '.$invoice->id);
        $this->line('invoice number: '.$invoice->invoice_number);
        $this->line('uuid: '.$invoice->uuid);
        $this->line('ICV: '.$invoice->icv);
        $this->line('taxpayer_type: '.$invoice->taxpayer_type);
        $this->line('payment_type: '.$invoice->payment_type);
        $this->line('invoice_scope: '.$invoice->invoice_scope);
        $this->line('invoice type code name: '.$typeCodes->nameFor($invoice));
        $this->line('tax: '.$invoice->tax_amount);
        $this->line('discount: '.$invoice->discount_amount);
        $this->line('next prepare command: php artisan jofotara:prepare '.$invoice->id);

        return self::SUCCESS;
    }

    private function resetInvoiceAutoIncrement(): void
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            DB::statement("DELETE FROM sqlite_sequence WHERE name = 'invoices'");
        } elseif ($driver === 'mysql') {
            DB::statement('ALTER TABLE invoices AUTO_INCREMENT = 1');
        }
    }
}
