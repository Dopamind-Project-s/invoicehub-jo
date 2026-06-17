<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\JofotaraService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class DebugSubmitJofotaraInvoice extends Command
{
    protected $signature = 'jofotara:debug-submit {invoice_id}';

    protected $description = 'Submit an invoice to JoFotara for debugging without updating invoice data.';

    public function handle(JofotaraService $jofotara): int
    {
        $invoice = Invoice::with(['seller', 'customer', 'items'])->find($this->argument('invoice_id'));

        if (! $invoice) {
            $this->error('Invoice not found.');

            return self::FAILURE;
        }

        $jofotara->ensureJofotaraIdentifiers($invoice);
        $xml = $jofotara->buildUblXml($invoice);
        $encodedXml = base64_encode($xml);
        $payload = ['invoice' => $encodedXml];
        $clientId = $jofotara->credential($invoice, 'client_id');
        $secretKey = $jofotara->credential($invoice, 'secret_key');
        $sourceId = $jofotara->credential($invoice, 'source_id');
        $taxNumber = $jofotara->credential($invoice, 'tax_number');

        $xmlPath = 'jofotara/last-submission-'.$invoice->id.'.xml';
        $payloadPath = 'jofotara/last-payload-'.$invoice->id.'.json';
        $jofotara->saveDebugFiles($xmlPath, $xml, $payloadPath, $payload);
        $xmlInfo = $jofotara->debugFileInfo($xmlPath);
        $payloadInfo = $jofotara->debugFileInfo($payloadPath);

        $this->line('endpoint: '.config('services.jofotara.url'));
        $this->line('invoice_number: '.$invoice->invoice_number);
        $this->line('jofotara_invoice_number: '.$invoice->jofotara_invoice_number);
        $this->line('jofotara_xml_uuid: '.$invoice->jofotara_xml_uuid);
        $this->line('client_id exists: '.(filled($clientId) ? 'yes' : 'no'));
        $this->line('secret key length: '.strlen((string) $secretKey));
        $this->line('source_id: '.$sourceId);
        $this->line('tax_number: '.$taxNumber);
        $this->line('XML length: '.strlen($xml));
        $this->line('base64 length: '.strlen($encodedXml));
        $this->line('payload keys: '.implode(', ', array_keys($payload)));
        $this->line('XML file path: '.$xmlInfo['path']);
        $this->line('XML file exists: '.($xmlInfo['exists'] ? 'yes' : 'no'));
        $this->line('XML file size: '.($xmlInfo['size'] ?? 0));
        $this->line('Payload file path: '.$payloadInfo['path']);
        $this->line('Payload file exists: '.($payloadInfo['exists'] ? 'yes' : 'no'));
        $this->line('Payload file size: '.($payloadInfo['size'] ?? 0));

        try {
            $response = Http::withHeaders([
                'Client-Id' => $clientId,
                'Secret-Key' => $secretKey,
                'Content-Type' => 'application/json',
                'Accept' => '*/*',
            ])->post(config('services.jofotara.url'), $payload);

            $this->line('HTTP status: '.$response->status());
            $this->line('raw response body length: '.strlen($response->body()));
            $this->line('raw response body:');
            $this->line($response->body() !== '' ? $response->body() : '[empty]');

            return $response->successful() ? self::SUCCESS : self::FAILURE;
        } catch (\Throwable $exception) {
            $this->line('HTTP status: FAILED');
            $this->line('raw response body length: '.strlen($exception->getMessage()));
            $this->line('raw response body:');
            $this->line($exception->getMessage());

            return self::FAILURE;
        }
    }
}
