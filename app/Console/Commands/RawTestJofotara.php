<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Throwable;

class RawTestJofotara extends Command
{
    protected $signature = 'jofotara:raw-test';

    protected $description = 'Run raw GET/POST diagnostics against JoFotara endpoints.';

    public function handle(): int
    {
        $baseUrl = 'https://backend.jofotara.gov.jo/';
        $invoiceUrl = 'https://backend.jofotara.gov.jo/core/invoices/';

        $this->line('A) GET '.$baseUrl);
        $this->runSafely(function () use ($baseUrl) {
            $response = Http::timeout((int) config('services.jofotara.timeout', 60))
                ->withOptions(['verify' => filter_var(config('services.jofotara.verify_ssl', true), FILTER_VALIDATE_BOOLEAN)])
                ->get($baseUrl);
            $this->line('status: '.$response->status());
            $this->line('headers: '.json_encode($response->headers(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            $this->line('body length: '.strlen($response->body()));
            $this->line('body preview: '.substr($response->body(), 0, 500));
        });

        $this->newLine();
        $this->line('B) POST '.$invoiceUrl);
        $this->runSafely(function () use ($invoiceUrl) {
            $response = Http::withHeaders([
                'Client-Id' => config('services.jofotara.client_id'),
                'Secret-Key' => config('services.jofotara.secret_key'),
                'Content-Type' => 'application/json',
                'Accept' => '*/*',
            ])->timeout((int) config('services.jofotara.timeout', 60))
                ->withOptions(['verify' => filter_var(config('services.jofotara.verify_ssl', true), FILTER_VALIDATE_BOOLEAN)])
                ->post($invoiceUrl, ['invoice' => 'TEST']);

            $this->line('status: '.$response->status());
            $this->line('response headers: '.json_encode($response->headers(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            $this->line('response body: '.($response->body() !== '' ? $response->body() : '[empty]'));
            $this->line('response body length: '.strlen($response->body()));
        });

        return self::SUCCESS;
    }

    private function runSafely(callable $callback): void
    {
        try {
            $callback();
        } catch (Throwable $exception) {
            if ($exception instanceof RequestException && $exception->response) {
                $response = $exception->response;
                $this->line('status: '.$response->status());
                $this->line('headers: '.json_encode($response->headers(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
                $this->line('body length: '.strlen($response->body()));
                $this->line('body preview: '.substr($response->body(), 0, 500));
            }
            $this->line('exception class: '.$exception::class);
            $this->line('message: '.$exception->getMessage());
            $this->line('code: '.$exception->getCode());
            $this->line('file: '.$exception->getFile());
            $this->line('line: '.$exception->getLine());
            $this->line('full stack trace:');
            $this->line($exception->getTraceAsString());
        }
    }
}
