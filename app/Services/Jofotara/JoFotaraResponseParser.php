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
        $errors = $this->first($body, ['EINV_RESULTS.ERRORS', 'EINV_RESULTS.ERRORS.ERROR', 'ERRORS', 'ERRORS.ERROR', 'errors', 'validationErrors', 'ErrorMessage', 'data.EINV_RESULTS.ERRORS', 'data.ERRORS']);
        $errorSummary = $this->summarize($errors) ?: ($response->failed() ? $this->summarize($message) : null);
        $statusText = strtoupper((string) $status);
        $validationText = strtoupper((string) $validationResult);
        $accepted = $response->successful() && $raw !== '' && ! $errorSummary && filled($uuid) && filled($qr) && $validationText !== 'ERROR' && (blank($status) || (! str_contains($statusText, 'NOT_SUBMITTED') && ! str_contains($statusText, 'REJECT') && ! str_contains($statusText, 'ERROR') && ! str_contains($statusText, 'FAIL')));

        return [
            'accepted' => $accepted,
            'empty' => $raw === '',
            'uuid' => $uuid,
            'qr' => $qr,
            'errors' => $errors,
            'error_summary' => $errorSummary,
            'status' => $status,
            'results' => $results,
            'validation_result' => $validationResult,
            'message' => $message,
            'body' => $body,
            'raw_response' => $raw,
            'warnings' => $accepted && blank($qr) ? ['Accepted but QR was not found in response.'] : [],
        ];
    }

    private function summarize(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if (is_scalar($value)) {
            return trim((string) $value) ?: null;
        }

        if (! is_array($value)) {
            return null;
        }

        $messages = [];
        foreach (['ERROR_MESSAGE', 'message', 'Message', 'error', 'ErrorMessage'] as $key) {
            $found = data_get($value, $key);
            if (is_scalar($found) && filled($found)) {
                $messages[] = trim((string) $found);
            }
        }

        foreach ($value as $item) {
            $summary = $this->summarize($item);
            if (filled($summary)) {
                $messages[] = $summary;
            }
        }

        $messages = array_values(array_unique(array_filter($messages)));

        return $messages === [] ? null : implode(' | ', $messages);
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
