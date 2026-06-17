<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\JofotaraService;
use Illuminate\Console\Command;

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

        $jofotara->ensureJofotaraIdentifiers($invoice);
        $xml = $jofotara->buildUblXml($invoice);
        $encodedXml = base64_encode($xml);
        $payload = ['invoice' => $encodedXml];
        $xmlPath = 'jofotara/last-submission-'.$invoice->id.'.xml';
        $payloadPath = 'jofotara/last-payload-'.$invoice->id.'.json';

        $jofotara->saveDebugFiles($xmlPath, $xml, $payloadPath, $payload);

        $headers = [
            'Client-Id' => $jofotara->credential($invoice, 'client_id'),
            'Secret-Key' => $this->maskSecret($jofotara->credential($invoice, 'secret_key')),
            'Content-Type' => 'application/json',
            'Accept' => '*/*',
        ];

        $this->line('Endpoint: '.config('services.jofotara.url'));
        $this->line('invoice_number: '.$invoice->invoice_number);
        $this->line('jofotara_invoice_number: '.$invoice->jofotara_invoice_number);
        $this->line('jofotara_xml_uuid: '.$invoice->jofotara_xml_uuid);
        $this->line('InvoiceTypeCode name: '.$jofotara->getInvoiceTypeCodeName($invoice));
        $this->line('ICV counter: '.$invoice->icv_counter);
        $this->line('SellerSupplierParty source id exists: '.(filled($jofotara->credential($invoice, 'source_id')) ? 'yes' : 'no'));
        $this->line('AdditionalDocumentReference exists: '.(filled($invoice->icv_counter) ? 'yes' : 'no'));
        $this->line('HTTP Method: POST');
        $this->line('Headers:');
        foreach ($headers as $key => $value) {
            $this->line('  '.$key.': '.$value);
        }
        $this->line('JSON payload:');
        $this->line(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        $xmlInfo = $jofotara->debugFileInfo($xmlPath);
        $payloadInfo = $jofotara->debugFileInfo($payloadPath);
        $this->line('XML file path: '.$xmlInfo['path']);
        $this->line('XML file exists: '.($xmlInfo['exists'] ? 'yes' : 'no'));
        $this->line('XML file size: '.($xmlInfo['size'] ?? 0));
        $this->line('Payload file path: '.$payloadInfo['path']);
        $this->line('Payload file exists: '.($payloadInfo['exists'] ? 'yes' : 'no'));
        $this->line('Payload file size: '.($payloadInfo['size'] ?? 0));
        $this->line('XML length: '.strlen($xml));
        $this->line('Base64 length: '.strlen($encodedXml));

        return self::SUCCESS;
    }

    private function maskSecret(?string $secret): string
    {
        return filled($secret) ? '[masked length '.strlen($secret).']' : '[missing]';
    }
}
