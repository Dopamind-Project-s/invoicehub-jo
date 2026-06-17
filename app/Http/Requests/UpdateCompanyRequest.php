<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $company = $this->route('company');

        return [
            'legal_name_ar' => ['required', 'string', 'max:255'],
            'legal_name_en' => ['nullable', 'string', 'max:255'],
            'trade_name' => ['nullable', 'string', 'max:255'],
            'tax_number' => ['required', 'string', 'max:50', Rule::unique('companies', 'tax_number')->ignore($company)],
            'jofotara_source_id' => ['nullable', 'string', 'max:50'],
            'jofotara_client_id' => ['nullable', 'string', 'max:100'],
            'jofotara_secret_key' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'country_code' => ['required', 'string', 'size:2'],
            'city' => ['nullable', 'string', 'max:255'],
            'street' => ['nullable', 'string', 'max:255'],
            'default_currency' => ['required', 'string', 'size:3'],
            'icv_prefix' => ['required', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
