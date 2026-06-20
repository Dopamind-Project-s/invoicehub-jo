<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceShare extends Model
{
    protected $fillable = ['invoice_id', 'company_id', 'token', 'channel', 'recipient', 'expires_at', 'created_by', 'last_accessed_at'];
    protected $casts = ['expires_at' => 'datetime', 'last_accessed_at' => 'datetime'];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
