<?php

declare(strict_types=1);

namespace App\Services\Jofotara;

use App\Models\Invoice;
use App\Models\InvoiceSubmissionLog;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use RuntimeException;

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
        $this->assertReadyForRealSubmission($prepared, $clientId, $secretKey);

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
        $officialUuid = filled($parsed['uuid']) ? (string) $parsed['uuid'] : null;
        $submissionUuid = $officialUuid ?: (string) Str::uuid();
        $successfulSubmission = $this->isSuccessfulSubmission($status, $parsed);

        InvoiceSubmissionLog::create([
            'invoice_id' => $preparedInvoice->id,
            'submission_uuid' => $submissionUuid,
            'status' => $status,
            'http_status' => $response->status(),
            'request_payload' => ['invoice_base64_length' => strlen($payload['invoice'])],
            'response_body' => $parsed['raw_response'],
            'error_message' => $parsed['error_summary'] ?? (is_scalar($parsed['errors']) ? (string) $parsed['errors'] : json_encode(['errors' => $parsed['errors'], 'warnings' => $parsed['warnings'] ?? []], JSON_UNESCAPED_UNICODE)),
            'attempt' => InvoiceSubmissionLog::where('invoice_id', $preparedInvoice->id)->count() + 1,
            'submitted_at' => now(),
        ]);

        $safeResponse = json_encode(['body' => $parsed['raw_response'] !== '' ? $parsed['raw_response'] : $parsed['body'], 'status' => $parsed['status'] ?? null, 'validation_result' => $parsed['validation_result'] ?? null, 'results' => $parsed['results'] ?? null, 'message' => $parsed['message'] ?? null, 'warnings' => $parsed['warnings'] ?? []], JSON_UNESCAPED_UNICODE);

        $preparedInvoice->forceFill([
            'submission_uuid' => $submissionUuid,
            'submission_response' => $safeResponse,
            'qr_code' => $parsed['qr'] ?: $preparedInvoice->qr_code,
            'submitted_at' => now(),
            'accepted_at' => $status === 'ACCEPTED' ? now() : null,
            'jofotara_status' => $status,
            'jofotara_validation_result' => $parsed['validation_result'] ?? null,
            'jofotara_uuid' => $officialUuid,
            'jofotara_qr' => $parsed['qr'] ?: null,
            'jofotara_response' => $safeResponse,
            'jofotara_submitted_at' => now(),
            'jofotara_error_message' => $successfulSubmission ? ($parsed['message'] ?? null) : ($parsed['error_summary'] ?? (is_scalar($parsed['errors']) ? (string) $parsed['errors'] : json_encode($parsed['errors'], JSON_UNESCAPED_UNICODE))),
        ])->save();

        if ($status === 'ACCEPTED' && $preparedInvoice->supplier && (int) $preparedInvoice->supplier->last_icv < (int) $preparedInvoice->icv) {
            $preparedInvoice->supplier->forceFill(['last_icv' => (int) $preparedInvoice->icv])->save();
        }

        return [
            'prepared' => $prepared,
            'response' => $response,
            'parsed' => $parsed,
            'status' => $status,
            'successful' => $successfulSubmission,
        ];
    }

    private function assertReadyForRealSubmission(array $prepared, mixed $clientId, mixed $secretKey): void
    {
        $invoice = $prepared['invoice'];
        $checks = $prepared['checks'];
        if ($invoice->issue_date && $invoice->issue_date->toDateString() > Carbon::today()->toDateString()) {
            throw new RuntimeException('لا يمكن إرسال فاتورة بتاريخ إصدار مستقبلي إلى نظام الفوترة الوطني.');
        }
        if (($checks['invoice_type_code_name'] ?? null) !== '021') {
            throw new RuntimeException('Refusing submit: InvoiceTypeCode name must be 021 for income receivable local invoices.');
        }
        if ($checks['buyer_fake_id_exists'] ?? false) {
            throw new RuntimeException('Refusing submit: fake buyer tax/national number detected.');
        }
        if (! ($checks['source_id_exists'] ?? false)) {
            throw new RuntimeException('Refusing submit: source_id is missing.');
        }
        if (! ($checks['seller_tax_number_exists'] ?? false)) {
            throw new RuntimeException('Refusing submit: seller tax number is missing.');
        }
        if (blank($clientId)) {
            throw new RuntimeException('Refusing submit: JOFOTARA_CLIENT_ID is missing.');
        }
        if (blank($secretKey)) {
            throw new RuntimeException('Refusing submit: JOFOTARA_SECRET_KEY is missing.');
        }
        if ((int) $invoice->icv > 1 && ($prepared['pih']['source'] ?? null) !== 'previous accepted invoice') {
            throw new RuntimeException('Refusing submit: PIH for ICV > 1 must come from previous ACCEPTED invoice.');
        }
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

        if (filled($parsed['status'] ?? null)) {
            return strtoupper((string) $parsed['status']);
        }

        return $parsed['accepted'] ? 'SUBMITTED' : 'REJECTED';
    }

    private function isSuccessfulSubmission(string $status, array $parsed): bool
    {
        return in_array($status, ['ACCEPTED', 'SUBMITTED'], true)
            && strtoupper((string) ($parsed['validation_result'] ?? '')) === 'PASS'
            && filled($parsed['qr'] ?? null)
            && filled($parsed['uuid'] ?? null)
            && blank($parsed['error_summary'] ?? null);
    }
}
