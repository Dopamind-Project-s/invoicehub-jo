<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\Jofotara\JoFotaraApiService;
use Illuminate\Console\Command;
use Throwable;

class SubmitRealJofotaraInvoice extends Command
{
    protected $signature = 'jofotara:submit-real {invoice_id}';

    protected $description = 'Submit a prepared invoice to the real JoFotara endpoint without exposing secrets.';

    public function handle(JoFotaraApiService $api): int
    {
        $invoice = Invoice::with(['supplier', 'customer', 'items'])->findOrFail($this->argument('invoice_id'));
        try {
            $result = $api->submit($invoice);
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $prepared = $result['prepared'];
        $response = $result['response'];
        $seller = $prepared['invoice']->supplier;
        $headers = collect($response->headers())->mapWithKeys(fn ($value, $key): array => [$key => implode(', ', $value)])->all();
        $body = $response->body();

        $this->line('endpoint: '.config('services.jofotara.url'));
        $this->line('client id exists: '.(filled($seller?->jofotara_client_id ?: config('services.jofotara.client_id')) ? 'yes' : 'no'));
        $this->line('secret key length: '.strlen((string) ($seller?->jofotara_secret_key ?: config('services.jofotara.secret_key'))));
        $this->line('source id: '.$seller?->jofotara_source_id);
        $this->line('tax number: '.$seller?->tax_number);
        $this->line('invoice number: '.$prepared['invoice']->invoice_number);
        $this->line('uuid: '.$prepared['invoice']->uuid);
        $this->line('icv: '.$prepared['invoice']->icv);
        $this->line('InvoiceTypeCode name: '.$prepared['invoice_type_code_name']);
        $this->line('PIH source: '.$prepared['pih']['source']);
        $this->line('XML length: '.$prepared['xml_length']);
        $this->line('Base64 length: '.$prepared['base64_length']);
        $this->line('HTTP status: '.$response->status());
        $this->line('response headers: '.json_encode($headers, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->line('response body length: '.strlen($body));
        $this->line('response body preview: '.str($body)->limit(500));

        if ($result['status'] === 'ACCEPTED') {
            $this->info('JoFotara accepted the invoice. Status updated to ACCEPTED.');
        } elseif ($result['status'] === 'ERROR') {
            $this->error('JoFotara returned empty 500. Inspect saved XML and payload files before retrying.');
        } else {
            $this->warn('JoFotara did not accept the invoice. Status updated to REJECTED.');
        }

        return $result['status'] === 'ERROR' ? self::FAILURE : self::SUCCESS;
    }
}
