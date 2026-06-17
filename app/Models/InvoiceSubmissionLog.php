<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceSubmissionLog extends Model
{
    protected $fillable = ['invoice_id', 'submission_uuid', 'status', 'http_status', 'request_payload', 'response_body', 'error_message', 'attempt', 'submitted_at'];

    protected $casts = ['request_payload' => 'array', 'submitted_at' => 'datetime'];
}
