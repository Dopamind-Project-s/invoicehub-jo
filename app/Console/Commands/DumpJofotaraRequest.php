<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\JofotaraService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DumpJofotaraRequest extends Command
{
    protected $signature = 'jofotara:dump-request {invoice_id}';

    protected $description = 'Dump the exact JoFotara endpoint, masked headers, JSON payload, and XML metadata.';

    public function handle(JofotaraService $jofotara): int
    {
        $invoice = Invoice::with(['seller', 'customer', 'items'])->find($this->argument('invoice_id'));

        if (! $invoice) {
            $this->error('Invoice not found.');

            return self::FAILURE;
        }

        $xml = $jofotara->buildUblXml($invoice);
        $encodedXml = base64_encode($xml);
        $payload = ['invoice' => $encodedXml];
        $xmlPath = 'jofotara/last-submission-'.$invoice->id.'.xml';
        $payloadPath = 'jofotara/last-payload-'.$invoice->id.'.json';

        Storage::disk('local')->put($xmlPath, $xml);
        Storage::disk('local')->put($payloadPath, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        $headers = [
            'Client-Id' => $jofotara->credential($invoice, 'client_id'),
            'Secret-Key' => $this->maskSecret($jofotara->credential($invoice, 'secret_key')),
            'Content-Type' => 'application/json',
            'Accept' => '*/*',
        ];

        $this->line('Endpoint: '.config('services.jofotara.url'));
        $this->line('HTTP Method: POST');
        $this->line('Headers:');
        foreach ($headers as $key => $value) {
            $this->line('  '.$key.': '.$value);
        }
        $this->line('JSON payload:');
        $this->line(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        $this->line('XML file path: storage/app/'.$xmlPath);
        $this->line('Payload file path: storage/app/'.$payloadPath);
        $this->line('XML length: '.strlen($xml));
        $this->line('Base64 length: '.strlen($encodedXml));

        return self::SUCCESS;
    }

    private function maskSecret(?string $secret): string
    {
        return filled($secret) ? '[masked length '.strlen($secret).']' : '[missing]';
    }
}
