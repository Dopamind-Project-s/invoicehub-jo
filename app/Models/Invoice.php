<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_CANCELLED = 'cancelled';

    public const TYPE_TAX_INVOICE = 'tax_invoice';
    public const TYPE_SIMPLIFIED_INVOICE = 'simplified_invoice';
    public const TYPE_CREDIT_NOTE = 'credit_note';
    public const TYPE_DEBIT_NOTE = 'debit_note';

    protected $fillable = ['company_id', 'contact_id', 'created_by', 'approved_by', 'approved_at', 'due_date', 'notes', 'tax_total', 'discount_total', 'grand_total', 'currency', 'uuid', 'invoice_number', 'icv', 'invoice_type', 'invoice_subtype', 'invoice_scope', 'payment_type', 'taxpayer_type', 'issue_date', 'issue_time', 'currency_code', 'currency', 'exchange_rate', 'supplier_id', 'customer_id', 'payment_method_id', 'subtotal', 'discount_amount', 'taxable_amount', 'tax_amount', 'total_amount', 'rounding_amount', 'payable_amount', 'previous_invoice_hash', 'xml_hash', 'qr_code', 'status', 'submission_uuid', 'submission_response', 'submitted_at', 'accepted_at'];

    protected $casts = ['issue_date' => 'date', 'due_date' => 'date', 'approved_at' => 'datetime', 'submitted_at' => 'datetime', 'accepted_at' => 'datetime', 'exchange_rate' => 'decimal:6', 'subtotal' => 'decimal:6', 'discount_amount' => 'decimal:6', 'taxable_amount' => 'decimal:6', 'tax_amount' => 'decimal:6', 'tax_total' => 'decimal:6', 'discount_total' => 'decimal:6', 'total_amount' => 'decimal:6', 'rounding_amount' => 'decimal:6', 'payable_amount' => 'decimal:6', 'grand_total' => 'decimal:6'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isReadOnly(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

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

    public function shares(): HasMany
    {
        return $this->hasMany(InvoiceShare::class);
    }

    public function xmlLogs(): HasMany
    {
        return $this->hasMany(InvoiceXmlLog::class);
    }
}
