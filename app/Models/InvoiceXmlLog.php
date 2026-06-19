<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceXmlLog extends Model
{
    protected $fillable = ['invoice_id', 'generated_xml', 'canonical_xml', 'hash', 'validation_result', 'submission_result', 'raw_response'];

    protected $casts = ['validation_result' => 'array', 'submission_result' => 'array'];
}
