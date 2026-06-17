<?php

declare(strict_types=1);

namespace App\Services\Jofotara;

use App\Models\Invoice;
use App\Models\InvoiceSubmissionLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class JoFotaraApiService
{
    public function submit(Invoice $invoice, string $xml): array
    {
        $invoice->loadMissing('supplier');
        $payload = ['invoice' => base64_encode($xml)];
        $response = Http::withHeaders(['Client-Id' => $invoice->supplier?->jofotara_client_id, 'Secret-Key' => $invoice->supplier?->jofotara_secret_key, 'Content-Type' => 'application/json'])->timeout((int) config('services.jofotara.timeout', 60))->post((string) config('services.jofotara.url'), $payload);
        $body = $response->json() ?? ['raw' => $response->body()];
        $accepted = $response->successful();
        $submissionUuid = (string) Str::uuid();
        InvoiceSubmissionLog::create(['invoice_id' => $invoice->id, 'submission_uuid' => $submissionUuid, 'status' => $accepted ? 'ACCEPTED' : 'REJECTED', 'http_status' => $response->status(), 'request_payload' => $payload, 'response_body' => $response->body(), 'attempt' => 1, 'submitted_at' => now()]);
        $invoice->forceFill(['status' => $accepted ? 'ACCEPTED' : 'REJECTED', 'submission_uuid' => $submissionUuid, 'submission_response' => json_encode($body, JSON_UNESCAPED_UNICODE), 'submitted_at' => now()])->save();

        return ['accepted' => $accepted, 'submission_uuid' => $submissionUuid, 'body' => $body];
    }

    public function status(Invoice $invoice): array
    {
        return ['status' => $invoice->status, 'submission_uuid' => $invoice->submission_uuid];
    }
}
