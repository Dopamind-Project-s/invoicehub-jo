<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\JofotaraService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PreviewJofotaraXml extends Command
{
    protected $signature = 'jofotara:preview-xml {invoice_id} {--save : Save XML to storage/app/jofotara/invoice-{id}.xml}';

    protected $description = 'Preview the UBL XML generated for a JoFotara invoice submission.';

    public function handle(JofotaraService $jofotara): int
    {
        $invoice = Invoice::with(['seller', 'customer', 'items'])->find($this->argument('invoice_id'));

        if (! $invoice) {
            $this->error('Invoice not found.');

            return self::FAILURE;
        }

        $jofotara->ensureJofotaraIdentifiers($invoice);
        $xml = $jofotara->buildUblXml($invoice);
        $this->line('invoice_number: '.$invoice->invoice_number);
        $this->line('jofotara_invoice_number: '.$invoice->jofotara_invoice_number);
        $this->line('jofotara_xml_uuid: '.$invoice->jofotara_xml_uuid);
        $this->line($xml);

        if ($this->option('save')) {
            $path = 'jofotara/invoice-'.$invoice->id.'.xml';
            Storage::build(['driver' => 'local', 'root' => storage_path('app')])->put($path, $xml);
            $this->info('XML saved to storage/app/'.$path);
        }

        return self::SUCCESS;
    }
}
