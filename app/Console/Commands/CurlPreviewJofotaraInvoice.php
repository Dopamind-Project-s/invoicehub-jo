<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\JofotaraService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CurlPreviewJofotaraInvoice extends Command
{
    protected $signature = 'jofotara:curl-preview {invoice_id}';

    protected $description = 'Print the equivalent curl command for the JoFotara invoice request.';

    public function handle(JofotaraService $jofotara): int
    {
        $invoice = Invoice::with(['seller', 'customer', 'items'])->find($this->argument('invoice_id'));

        if (! $invoice) {
            $this->error('Invoice not found.');

            return self::FAILURE;
        }

        $xml = $jofotara->buildUblXml($invoice);
        $payload = ['invoice' => base64_encode($xml)];
        Storage::disk('local')->put('jofotara/last-submission-'.$invoice->id.'.xml', $xml);
        Storage::disk('local')->put('jofotara/last-payload-'.$invoice->id.'.json', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        $command = [
            'curl',
            '-X', 'POST',
            config('services.jofotara.url'),
            '-H', 'Client-Id: '.$jofotara->credential($invoice, 'client_id'),
            '-H', 'Secret-Key: [MASKED length '.strlen((string) $jofotara->credential($invoice, 'secret_key')).']',
            '-H', 'Content-Type: application/json',
            '-H', 'Accept: */*',
            '--data', json_encode($payload, JSON_UNESCAPED_UNICODE),
        ];

        $this->line(collect($command)->map(fn ($part) => escapeshellarg((string) $part))->implode(' '));
        $this->line('Note: Secret-Key is masked; replace the masked value before running manually.');

        return self::SUCCESS;
    }
}
