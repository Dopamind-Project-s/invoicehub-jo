<?php

declare(strict_types=1);

namespace App\Services\Audit;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     */
    public function record(string $action, ?Model $model = null, array $before = [], array $after = [], ?Request $request = null, ?int $userId = null): AuditLog
    {
        $request ??= request();

        return AuditLog::create([
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'auditable_type' => $model?->getMorphClass(),
            'auditable_id' => $model?->getKey(),
            'before_values' => $this->sanitize($before),
            'after_values' => $this->sanitize($after),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function sanitize(array $values): array
    {
        $blocked = [
            'password',
            'remember_token',
            'jofotara_client_id',
            'jofotara_secret_key',
            'secret',
            'secret_key',
            'token',
        ];

        foreach ($values as $key => $value) {
            if (in_array(strtolower((string) $key), $blocked, true)) {
                $values[$key] = '[redacted]';
            } elseif (is_array($value)) {
                $values[$key] = $this->sanitize($value);
            }
        }

        return Arr::where($values, fn ($value): bool => $value !== null);
    }
}
