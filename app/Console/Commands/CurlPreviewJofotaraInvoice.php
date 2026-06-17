<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\JofotaraService;
use Illuminate\Console\Command;

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

        $jofotara->ensureJofotaraIdentifiers($invoice);
        $xml = $jofotara->buildUblXml($invoice);
        $payload = ['invoice' => base64_encode($xml)];
        $xmlPath = 'jofotara/last-submission-'.$invoice->id.'.xml';
        $payloadPath = 'jofotara/last-payload-'.$invoice->id.'.json';
        $jofotara->saveDebugFiles($xmlPath, $xml, $payloadPath, $payload);

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

        $xmlInfo = $jofotara->debugFileInfo($xmlPath);
        $payloadInfo = $jofotara->debugFileInfo($payloadPath);
        $this->line('invoice_number: '.$invoice->invoice_number);
        $this->line('jofotara_invoice_number: '.$invoice->jofotara_invoice_number);
        $this->line('jofotara_xml_uuid: '.$invoice->jofotara_xml_uuid);
        $this->line('XML file path: '.$xmlInfo['path']);
        $this->line('XML file exists: '.($xmlInfo['exists'] ? 'yes' : 'no'));
        $this->line('XML file size: '.($xmlInfo['size'] ?? 0));
        $this->line('Payload file path: '.$payloadInfo['path']);
        $this->line('Payload file exists: '.($payloadInfo['exists'] ? 'yes' : 'no'));
        $this->line('Payload file size: '.($payloadInfo['size'] ?? 0));
        $this->line(collect($command)->map(fn ($part) => escapeshellarg((string) $part))->implode(' '));
        $this->line('Note: Secret-Key is masked; replace the masked value before running manually.');

        return self::SUCCESS;
    }
}
