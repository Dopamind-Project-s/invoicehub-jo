<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanySetting extends Model
{
    protected $fillable = ['company_id', 'category', 'key', 'value'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
