<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\Jofotara\JoFotaraPreparationService;
use Illuminate\Console\Command;
use Throwable;

class PrepareJofotaraInvoice extends Command
{
    protected $signature = 'jofotara:prepare {invoice_id}';

    protected $description = 'Prepare JoFotara XML, payload, canonical XML and local validation artifacts.';

    public function handle(JoFotaraPreparationService $preparer): int
    {
        $invoice = Invoice::with(['supplier', 'customer', 'items'])->findOrFail($this->argument('invoice_id'));
        try {
            $prepared = $preparer->prepare($invoice);
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
        $invoice = $prepared['invoice'];
        $checks = $prepared['checks'];
        $pih = $prepared['pih'];
        if (filled($pih['warning'] ?? null)) {
            $this->warn($pih['warning']);
        }
        $this->line('invoice id: '.$invoice->id);
        $this->line('invoice number: '.$invoice->invoice_number);
        $this->line('UUID: '.$invoice->uuid);
        $this->line('ICV: '.$invoice->icv);
        $this->line('taxpayer_type: '.$invoice->taxpayer_type);
        $this->line('payment_type: '.$invoice->payment_type);
        $this->line('invoice_scope: '.$invoice->invoice_scope);
        $this->line('invoice type code name: '.$prepared['invoice_type_code_name']);
        $this->line('seller tax number: '.$invoice->supplier?->tax_number);
        $this->line('source id: '.$invoice->supplier?->jofotara_source_id);
        $this->line('customer has fake tax id: '.$this->yesNo($checks['buyer_fake_id_exists']));
        $this->line('previous accepted invoice found: '.$this->yesNo((bool) ($pih['previous_accepted_invoice_found'] ?? false)));
        $this->line('PIH source: '.$pih['source']);
        $this->line('PIH value length: '.strlen((string) $pih['value']));
        $this->line('PIH exists: '.$this->yesNo($checks['pih_exists']));
        $this->line('SellerSupplierParty exists: '.$this->yesNo($checks['seller_supplier_party_exists']));
        $this->line('buyer fake id exists: '.$this->yesNo($checks['buyer_fake_id_exists']));
        $this->line('TaxAmount: '.$invoice->tax_amount);
        $this->line('Discount: '.$invoice->discount_amount);
        $this->line('XML path: '.$prepared['xml_path']);
        $this->line('payload path: '.$prepared['payload_path']);
        $this->line('XML length: '.$prepared['xml_length']);
        $this->line('Base64 length: '.$prepared['base64_length']);
        $this->line('XML SHA256: '.$prepared['xml_sha256']);

        return self::SUCCESS;
    }

    private function yesNo(bool $value): string
    {
        return $value ? 'yes' : 'no';
    }
}
