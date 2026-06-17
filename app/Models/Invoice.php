<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = ['invoice_number', 'seller_id', 'customer_id', 'invoice_date', 'due_date', 'subtotal', 'tax_total', 'discount_total', 'total', 'payment_reference', 'status', 'jofotara_uuid', 'jofotara_qr', 'jofotara_response', 'submitted_at'];

    protected $casts = ['invoice_date' => 'date', 'due_date' => 'date', 'submitted_at' => 'datetime'];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
