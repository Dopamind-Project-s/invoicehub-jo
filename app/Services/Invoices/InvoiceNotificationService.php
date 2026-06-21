<?php

namespace App\Services\Invoices;

use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceNotificationService
{
    public function record(Invoice $invoice, string $event, ?int $userId = null): void
    {
        if (! DB::getSchemaBuilder()->hasTable('notifications')) {
            return;
        }

        DB::table('notifications')->insert([
            'id' => (string) Str::uuid(),
            'type' => 'invoice.'.$event,
            'notifiable_type' => 'App\\Models\\Company',
            'notifiable_id' => $invoice->company_id,
            'data' => json_encode([
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'status' => $invoice->status,
                'event' => $event,
                'user_id' => $userId,
            ], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
