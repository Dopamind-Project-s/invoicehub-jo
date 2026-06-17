<?php

declare(strict_types=1);

namespace App\Services\Jofotara;

use App\Models\Invoice;
use App\Models\InvoiceSubmissionLog;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class JoFotaraApiService
{
    public function __construct(
        private readonly JoFotaraPreparationService $preparer,
        private readonly JoFotaraResponseParser $parser,
    ) {}

    public function submit(Invoice $invoice): array
    {
        $prepared = $this->preparer->prepare($invoice);
        /** @var Invoice $preparedInvoice */
        $preparedInvoice = $prepared['invoice'];
        $endpoint = (string) config('services.jofotara.url', 'https://backend.jofotara.gov.jo/core/invoices/');
        $clientId = $preparedInvoice->supplier?->jofotara_client_id ?: config('services.jofotara.client_id');
        $secretKey = $preparedInvoice->supplier?->jofotara_secret_key ?: config('services.jofotara.secret_key');
        $payload = $prepared['payload'];

        try {
            $response = Http::withHeaders([
                'Client-Id' => $clientId,
                'Secret-Key' => $secretKey,
                'Content-Type' => 'application/json',
                'Accept' => '*/*',
            ])->timeout((int) config('services.jofotara.timeout', 60))
                ->withOptions(['verify' => filter_var(config('services.jofotara.verify_ssl', true), FILTER_VALIDATE_BOOLEAN)])
                ->post($endpoint, $payload);
        } catch (RequestException $exception) {
            $response = $exception->response;
            if (! $response) {
                throw $exception;
            }
        }

        $parsed = $this->parser->parse($response);
        $status = $this->statusFrom($response->status(), $parsed);
        $submissionUuid = filled($parsed['uuid']) ? (string) $parsed['uuid'] : (string) Str::uuid();

        InvoiceSubmissionLog::create([
            'invoice_id' => $preparedInvoice->id,
            'submission_uuid' => $submissionUuid,
            'status' => $status,
            'http_status' => $response->status(),
            'request_payload' => ['invoice_base64_length' => strlen($payload['invoice'])],
            'response_body' => $parsed['raw_response'],
            'error_message' => is_scalar($parsed['errors']) ? (string) $parsed['errors'] : json_encode($parsed['errors'], JSON_UNESCAPED_UNICODE),
            'attempt' => InvoiceSubmissionLog::where('invoice_id', $preparedInvoice->id)->count() + 1,
            'submitted_at' => now(),
        ]);

        $preparedInvoice->forceFill([
            'status' => $status,
            'submission_uuid' => $submissionUuid,
            'submission_response' => $parsed['raw_response'] !== '' ? $parsed['raw_response'] : json_encode($parsed['body'], JSON_UNESCAPED_UNICODE),
            'qr_code' => $parsed['qr'] ?: $preparedInvoice->qr_code,
            'submitted_at' => now(),
            'accepted_at' => $status === 'ACCEPTED' ? now() : null,
        ])->save();

        return [
            'prepared' => $prepared,
            'response' => $response,
            'parsed' => $parsed,
            'status' => $status,
        ];
    }

    public function status(Invoice $invoice): array
    {
        return ['status' => $invoice->status, 'submission_uuid' => $invoice->submission_uuid];
    }

    private function statusFrom(int $httpStatus, array $parsed): string
    {
        if ($httpStatus === 500 && $parsed['empty']) {
            return 'ERROR';
        }

        return $parsed['accepted'] ? 'ACCEPTED' : 'REJECTED';
    }
}
