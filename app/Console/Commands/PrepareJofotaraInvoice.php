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
        $checks = $prepared['checks'];
        $this->line('XML path: '.$prepared['xml_path']);
        $this->line('payload path: '.$prepared['payload_path']);
        $this->line('XML length: '.$prepared['xml_length']);
        $this->line('Base64 length: '.$prepared['base64_length']);
        $this->line('XML SHA256: '.$prepared['xml_sha256']);
        $this->line('previous invoice hash: '.($prepared['previous_invoice_hash'] ?: '[initial empty PIH]'));
        $this->line('invoice type code name: '.$prepared['invoice_type_code_name']);
        $this->line('source id exists: '.$this->yesNo($checks['source_id_exists']));
        $this->line('ICV exists: '.$this->yesNo($checks['icv_exists']));
        $this->line('PIH exists: '.$this->yesNo($checks['pih_exists']));
        $this->line('SellerSupplierParty exists: '.$this->yesNo($checks['seller_supplier_party_exists']));
        $this->line('buyer fake id exists: '.$this->yesNo($checks['buyer_fake_id_exists']));

        return self::SUCCESS;
    }

    private function yesNo(bool $value): string
    {
        return $value ? 'yes' : 'no';
    }
}
