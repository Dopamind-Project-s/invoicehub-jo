<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = ['uuid', 'invoice_number', 'icv', 'invoice_type', 'invoice_subtype', 'invoice_scope', 'payment_type', 'taxpayer_type', 'issue_date', 'issue_time', 'currency_code', 'exchange_rate', 'supplier_id', 'customer_id', 'payment_method_id', 'subtotal', 'discount_amount', 'taxable_amount', 'tax_amount', 'total_amount', 'rounding_amount', 'payable_amount', 'previous_invoice_hash', 'xml_hash', 'qr_code', 'status', 'submission_uuid', 'submission_response', 'submitted_at', 'accepted_at'];

    protected $casts = ['issue_date' => 'date', 'submitted_at' => 'datetime', 'accepted_at' => 'datetime', 'exchange_rate' => 'decimal:6', 'subtotal' => 'decimal:6', 'discount_amount' => 'decimal:6', 'taxable_amount' => 'decimal:6', 'tax_amount' => 'decimal:6', 'total_amount' => 'decimal:6', 'rounding_amount' => 'decimal:6', 'payable_amount' => 'decimal:6'];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'supplier_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function xmlLogs(): HasMany
    {
        return $this->hasMany(InvoiceXmlLog::class);
    }
}
