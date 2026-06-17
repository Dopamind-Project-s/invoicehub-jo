<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Seller;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $seller = Seller::firstOrCreate(
            ['name' => 'مطبخ ومشاوي جوهرة الجبيهة'],
            ['is_default' => true]
        );

        if (! $seller->is_default && Seller::where('is_default', true)->doesntExist()) {
            $seller->update(['is_default' => true]);
        }

        $customer = Customer::firstOrCreate(
            ['name' => 'شركة الاتصالات الاردنيه'],
            ['tax_number' => '000000000', 'phone' => '0790000000', 'address' => 'عمّان - الأردن']
        );

        $invoiceNumber = 'INV/'.now()->year.'/00001';
        $invoice = Invoice::firstOrCreate(
            ['invoice_number' => $invoiceNumber],
            ['seller_id' => $seller->id, 'customer_id' => $customer->id, 'jofotara_invoice_number' => 'INV_'.now()->year.'_00001', 'jofotara_xml_uuid' => (string) Str::uuid(), 'invoice_date' => now()->toDateString(), 'subtotal' => 71.400, 'tax_total' => 0, 'discount_total' => 0, 'total' => 71.400, 'payment_reference' => $invoiceNumber, 'payment_type' => 'receivable', 'taxpayer_type' => 'general_sales', 'icv_counter' => 1]
        );

        if (! $invoice->seller_id) {
            $invoice->update(['seller_id' => $seller->id]);
        }

        if ($invoice->items()->doesntExist()) {
            $invoice->items()->createMany([
                ['description' => 'وجبة منسف لحم بلدي', 'quantity' => 8, 'unit_price' => 8, 'tax_rate' => 0, 'tax_amount' => 0, 'line_total' => 64],
                ['description' => 'ماتريكس', 'quantity' => 8, 'unit_price' => 0.300, 'tax_rate' => 0, 'tax_amount' => 0, 'line_total' => 2.400],
                ['description' => 'توصيل', 'quantity' => 1, 'unit_price' => 5, 'tax_rate' => 0, 'tax_amount' => 0, 'line_total' => 5],
            ]);
        }
    }
}
