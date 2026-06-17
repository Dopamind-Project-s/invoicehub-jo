<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\JofotaraService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class TestJofotaraVariants extends Command
{
    protected $signature = 'jofotara:test-variants {invoice_id}';

    protected $description = 'Send safe JoFotara request variants for debugging without changing normal submission behavior.';

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
        $endpoint = config('services.jofotara.url');

        Storage::disk('local')->put('jofotara/test-variants-'.$invoice->id.'.xml', $xml);

        $this->line('endpoint: '.$endpoint);
        $this->line('invoice_id: '.$invoice->id);
        $this->line('client_id exists: '.(filled($clientId) ? 'yes' : 'no'));
        $this->line('secret key length: '.strlen((string) $secretKey));
        $this->line('XML length: '.strlen($xml));
        $this->line('base64 length: '.strlen($encodedXml));

        $variants = [
            'A' => fn () => Http::withHeaders([
                'Client-Id' => $clientId,
                'Secret-Key' => $secretKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($endpoint, $payload),
            'B' => fn () => Http::withHeaders([
                'Client-Id' => $clientId,
                'Secret-Key' => $secretKey,
                'Content-Type' => 'application/json',
                'Accept' => '*/*',
            ])->post($endpoint, $payload),
            'C' => fn () => Http::withHeaders([
                'client_id' => $clientId,
                'client_secret' => $secretKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($endpoint, $payload),
            'D' => fn () => Http::withHeaders([
                'Client-Id' => $clientId,
                'Secret-Key' => $secretKey,
            ])->asJson()->post($endpoint, $payload),
        ];

        foreach ($variants as $variant => $send) {
            Storage::disk('local')->put('jofotara/variant-'.$variant.'.json', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            $this->newLine();
            $this->line('Variant '.$variant);

            try {
                $response = $send();
                $body = $response->body();
                $this->line('HTTP status: '.$response->status());
                $this->line('body length: '.strlen($body));
                $this->line('body:');
                $this->line($body !== '' ? $body : '[empty]');
            } catch (\Throwable $exception) {
                $this->line('HTTP status: FAILED');
                $this->line('body length: '.strlen($exception->getMessage()));
                $this->line('body:');
                $this->line($exception->getMessage());
            }
        }

        return self::SUCCESS;
    }
}
