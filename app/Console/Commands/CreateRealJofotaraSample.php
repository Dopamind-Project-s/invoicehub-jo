<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\Jofotara\ICVService;
use App\Services\Jofotara\TaxCalculationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CreateRealJofotaraSample extends Command
{
    protected $signature = 'jofotara:create-real-sample';

    protected $description = 'Create a real JoFotara-ready sample invoice for the configured Jordan seller.';

    public function handle(ICVService $icv, TaxCalculationService $tax): int
    {
        $invoice = DB::transaction(function () use ($icv, $tax): Invoice {
            $companyData = [
                'legal_name_ar' => 'مطبخ ومشاوي جوهرة الجبيهة',
                'legal_name_en' => 'Jawharat Al Jubaiha Kitchen and Grills',
                'trade_name' => 'مطبخ ومشاوي جوهرة الجبيهة',
                'national_number' => null,
                'registration_number' => null,
                'branch_code' => '0',
                'country_code' => 'JO',
                'city' => 'Jordan',
                'street' => 'Jordan',
                'phone' => '799021616',
                'default_currency' => 'JOD',
                'icv_prefix' => 'INV',
                'jofotara_client_id' => config('services.jofotara.client_id'),
                'jofotara_source_id' => '18412122',
                'is_active' => true,
            ];
            if (filled(config('services.jofotara.secret_key'))) {
                $companyData['jofotara_secret_key'] = config('services.jofotara.secret_key');
            }
            $company = Company::updateOrCreate(['tax_number' => '9578331'], $companyData);

            $customer = Customer::updateOrCreate(
                ['name' => 'عميل نقدي', 'tax_number' => null],
                ['customer_type' => 'INDIVIDUAL', 'country_code' => 'JO', 'city' => 'Jordan', 'address' => 'Jordan', 'postal_code' => '-', 'phone' => '-', 'is_taxable' => true]
            );

            $items = [
                ['description' => 'وجبة منسف لحم بلدي', 'quantity' => '1.000000000', 'unit_price' => '7.000000000', 'discount' => '0.000000000', 'tax_category' => 'S', 'tax_percent' => '16.000000000'],
                ['description' => 'ماتريكس', 'quantity' => '1.000000000', 'unit_price' => '1.000000000', 'discount' => '0.000000000', 'tax_category' => 'S', 'tax_percent' => '16.000000000'],
                ['description' => 'توصيل', 'quantity' => '1.000000000', 'unit_price' => '1.000000000', 'discount' => '0.000000000', 'tax_category' => 'S', 'tax_percent' => '16.000000000'],
            ];
            $totals = $tax->calculateInvoice($items);
            $number = 'INV_'.now()->year.'_'.str_pad((string) (Invoice::whereYear('created_at', now()->year)->count() + 1), 5, '0', STR_PAD_LEFT);
            $invoice = Invoice::create(array_merge($totals, [
                'uuid' => (string) Str::uuid(),
                'invoice_number' => $number,
                'icv' => $icv->next($company),
                'invoice_type' => 'STANDARD',
                'invoice_subtype' => 'SALE',
                'invoice_scope' => 'local',
                'payment_type' => 'receivable',
                'taxpayer_type' => 'general_sales',
                'issue_date' => now()->toDateString(),
                'issue_time' => now()->format('H:i:s'),
                'currency_code' => 'JOD',
                'exchange_rate' => '1.000000000',
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

        $this->warnIfSecretColumnLooksTooSmall();
        $this->info('created invoice id: '.$invoice->id);
        $this->line('invoice number: '.$invoice->invoice_number);
        $this->line('uuid: '.$invoice->uuid);
        $this->line('icv: '.$invoice->icv);
        $this->line('total: '.$invoice->payable_amount);
        $this->line('status: '.$invoice->status);
        $this->line('next prepare command: php artisan jofotara:prepare '.$invoice->id);
        $this->line('next submit command: php artisan jofotara:submit-real '.$invoice->id);

        return self::SUCCESS;
    }

    private function warnIfSecretColumnLooksTooSmall(): void
    {
        foreach (Schema::getColumns('companies') as $column) {
            if (($column['name'] ?? null) === 'jofotara_secret_key' && (($column['type_name'] ?? '') === 'varchar' || (int) ($column['length'] ?? 0) > 0 && (int) ($column['length'] ?? 0) < 1000)) {
                $this->warn('jofotara_secret_key column may be too small for production keys.');
            }
        }
    }
}
