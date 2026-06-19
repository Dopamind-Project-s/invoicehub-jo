<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\Jofotara\ICVService;
use App\Services\Jofotara\InvoiceTypeCodeService;
use App\Services\Jofotara\TaxCalculationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateMansafJofotaraInvoice extends Command
{
    protected $signature = 'jofotara:create-mansaf-invoice';

    protected $description = 'Create a draft Mansaf JoFotara invoice totaling 162.700 JOD.';

    public function handle(TaxCalculationService $tax, ICVService $icv, InvoiceTypeCodeService $typeCodes): int
    {
        $invoice = DB::transaction(function () use ($tax, $icv): Invoice {
            $company = Company::where('is_active', true)->firstOrFail();
            $customer = Customer::firstOrCreate(['name' => 'عميل نقدي'], ['customer_type' => 'INDIVIDUAL', 'country_code' => 'JO', 'phone' => '-']);
            Invoice::query()->where('supplier_id', $company->id)->whereIn('status', ['DRAFT', 'ERROR', 'REJECTED'])->delete();
            if (Invoice::query()->count() === 0) {
                $this->resetInvoiceAutoIncrement();
                $company->forceFill(['last_icv' => 0])->save();
            }
            $items = [
                ['description' => 'وجبة منسف لحمة بلدية', 'quantity' => '19.000000000', 'unit_price' => '8.000000000', 'discount' => '0.000000000', 'tax_category' => 'Z', 'tax_percent' => '0.000000000'],
                ['description' => 'ماتريكس', 'quantity' => '19.000000000', 'unit_price' => '0.300000000', 'discount' => '0.000000000', 'tax_category' => 'Z', 'tax_percent' => '0.000000000'],
                ['description' => 'توصيل', 'quantity' => '1.000000000', 'unit_price' => '5.000000000', 'discount' => '0.000000000', 'tax_category' => 'Z', 'tax_percent' => '0.000000000'],
            ];
            $invoice = Invoice::create(array_merge($tax->calculateInvoice($items), [
                'uuid' => (string) Str::uuid(),
                'invoice_number' => 'INV_'.now()->year.'_'.str_pad((string) (Invoice::whereYear('created_at', now()->year)->count() + 1), 5, '0', STR_PAD_LEFT),
                'icv' => $icv->next($company),
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
                $invoice->items()->create(array_merge($item, ['line_extension_amount' => $line['line_extension_amount'], 'tax_amount' => $line['tax_amount'], 'line_total' => $line['line_total']]));
            }

            return $invoice;
        });

        $this->line('invoice id: '.$invoice->id);
        $this->line('invoice number: '.$invoice->invoice_number);
        $this->line('invoice type code name: '.$typeCodes->nameFor($invoice));
        $this->line('total: '.$invoice->payable_amount);
        $this->line('next prepare command: php artisan jofotara:prepare '.$invoice->id);
        $this->line('next submit command: php artisan jofotara:submit-real '.$invoice->id);

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
