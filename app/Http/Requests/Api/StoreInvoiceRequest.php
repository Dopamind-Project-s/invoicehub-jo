<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['supplier_id' => ['required', 'exists:companies,id'], 'customer_id' => ['nullable', 'exists:customers,id'], 'invoice_number' => ['required', 'string', 'unique:invoices,invoice_number'], 'invoice_type' => ['required', 'in:STANDARD,SIMPLIFIED'], 'invoice_subtype' => ['required', 'in:SALE,RETURN,DEBIT_NOTE,CREDIT_NOTE'], 'currency_code' => ['required', 'size:3'], 'items' => ['required', 'array', 'min:1'], 'items.*.description' => ['required', 'string'], 'items.*.quantity' => ['required', 'numeric', 'min:0.000001'], 'items.*.unit_price' => ['required', 'numeric', 'min:0'], 'items.*.discount' => ['nullable', 'numeric', 'min:0'], 'items.*.tax_category' => ['required', 'string'], 'items.*.tax_percent' => ['required', 'numeric', 'min:0']];
    }
}
