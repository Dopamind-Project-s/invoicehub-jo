<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = ['invoice_id', 'product_id', 'description', 'quantity', 'unit_price', 'discount', 'discount_amount', 'line_extension_amount', 'tax_category', 'tax_percent', 'tax_amount', 'line_total'];

    protected $casts = ['quantity' => 'decimal:6', 'unit_price' => 'decimal:6', 'discount' => 'decimal:6', 'discount_amount' => 'decimal:6', 'line_extension_amount' => 'decimal:6', 'tax_percent' => 'decimal:6', 'tax_amount' => 'decimal:6', 'line_total' => 'decimal:6'];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
