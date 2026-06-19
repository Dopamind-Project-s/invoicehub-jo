<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use Database\Seeders\CompanySeeder;
use Database\Seeders\CountrySeeder;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\CustomerSeeder;
use Database\Seeders\InvoiceSeeder;
use Database\Seeders\PaymentMethodSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\TaxCategorySeeder;
use Database\Seeders\UnitSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SeedJofotaraDemo extends Command
{
    protected $signature = 'jofotara:seed-demo';

    protected $description = 'Run safe demo seeders for the JoFotara sample company, customer, products, and draft invoice.';

    public function handle(): int
    {
        foreach ([CountrySeeder::class, CurrencySeeder::class, UnitSeeder::class, TaxCategorySeeder::class, PaymentMethodSeeder::class, CompanySeeder::class, CustomerSeeder::class, ProductSeeder::class, InvoiceSeeder::class] as $seeder) {
            Artisan::call('db:seed', ['--class' => $seeder, '--force' => true]);
        }

        $company = Company::where('tax_number', '9578331')->firstOrFail();
        $customer = Customer::where('name', 'عميل نقدي')->firstOrFail();
        $invoice = Invoice::where('invoice_number', 'INV_2026_00001')->firstOrFail();

        $this->line('company id: '.$company->id);
        $this->line('customer id: '.$customer->id);
        $this->line('invoice id: '.$invoice->id);
        $this->line('invoice number: '.$invoice->invoice_number);
        $this->line('total: '.$invoice->payable_amount);
        $this->line('next prepare command: php artisan jofotara:prepare '.$invoice->id);

        return self::SUCCESS;
    }
}
