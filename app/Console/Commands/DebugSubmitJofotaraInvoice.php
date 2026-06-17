<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\JofotaraService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

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

        $xml = $jofotara->buildUblXml($invoice);
        $encodedXml = base64_encode($xml);
        $payload = ['invoice' => $encodedXml];
        $clientId = $jofotara->credential($invoice, 'client_id');
        $secretKey = $jofotara->credential($invoice, 'secret_key');
        $sourceId = $jofotara->credential($invoice, 'source_id');
        $taxNumber = $jofotara->credential($invoice, 'tax_number');

        Storage::disk('local')->put('jofotara/debug-submission-'.$invoice->id.'.xml', $xml);
        Storage::disk('local')->put('jofotara/debug-payload-'.$invoice->id.'.json', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        $this->line('endpoint: '.config('services.jofotara.url'));
        $this->line('client_id exists: '.(filled($clientId) ? 'yes' : 'no'));
        $this->line('secret key length: '.strlen((string) $secretKey));
        $this->line('source_id: '.$sourceId);
        $this->line('tax_number: '.$taxNumber);
        $this->line('XML length: '.strlen($xml));
        $this->line('base64 length: '.strlen($encodedXml));
        $this->line('payload keys: '.implode(', ', array_keys($payload)));

        $response = Http::withHeaders([
            'Client-Id' => $clientId,
            'Secret-Key' => $secretKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post(config('services.jofotara.url'), $payload);

        $this->line('HTTP status: '.$response->status());
        $this->line('raw response body length: '.strlen($response->body()));
        $this->line('raw response body:');
        $this->line($response->body() !== '' ? $response->body() : '[empty]');

        return $response->successful() ? self::SUCCESS : self::FAILURE;
    }
}
