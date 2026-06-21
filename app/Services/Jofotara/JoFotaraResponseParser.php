<?php

declare(strict_types=1);

namespace App\Services\Jofotara;

use Illuminate\Http\Client\Response;

class JoFotaraResponseParser
{
    public function parse(Response $response): array
    {
        $raw = $response->body();
        $json = $response->json();
        $body = is_array($json) ? $json : ['raw' => $raw];
        $uuid = $this->first($body, ['uuid', 'invoice_uuid', 'EINV_INV_UUID', 'EINV_NUM', 'submission_uuid', 'data.EINV_INV_UUID']);
        $qr = $this->first($body, ['qr', 'qr_code', 'EINV_QR', 'invoiceQr', 'QR', 'data.qr', 'data.qr_code', 'data.EINV_QR']);
        $status = $this->first($body, ['EINV_STATUS', 'status', 'data.EINV_STATUS']);
        $results = $this->first($body, ['EINV_RESULTS', 'results', 'data.EINV_RESULTS']);
        $validationResult = is_array($results) ? $this->first($results, ['status', 'Status']) : $results;
        $message = $this->first($body, ['EINV_MESSAGE', 'message', 'data.EINV_MESSAGE']);
        $errors = $this->first($body, ['errors', 'validationErrors', 'ErrorMessage']) ?: ($response->failed() ? $message : null);
        $statusText = strtoupper((string) $status);
        $accepted = $response->successful() && $raw !== '' && ! $errors && (blank($status) || (! str_contains($statusText, 'REJECT') && ! str_contains($statusText, 'ERROR') && ! str_contains($statusText, 'FAIL')));

        return [
            'accepted' => $accepted,
            'empty' => $raw === '',
            'uuid' => $uuid,
            'qr' => $qr,
            'errors' => $errors,
            'status' => $status,
            'results' => $results,
            'validation_result' => $validationResult,
            'message' => $message,
            'body' => $body,
            'raw_response' => $raw,
            'warnings' => $accepted && blank($qr) ? ['Accepted but QR was not found in response.'] : [],
        ];
    }

    private function first(array $body, array $keys): mixed
    {
        foreach ($keys as $key) {
            $value = data_get($body, $key);
            if (filled($value)) {
                return $value;
            }
        }

        foreach ($body as $value) {
            if (is_array($value)) {
                $nested = $this->first($value, $keys);
                if (filled($nested)) {
                    return $nested;
                }
            }
        }

        return null;
    }
}
