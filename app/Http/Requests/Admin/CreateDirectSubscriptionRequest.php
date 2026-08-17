<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateDirectSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() === true;
    }

    public function rules(): array
    {
        return ['plan_id' => ['required', 'integer', 'exists:plans,id'], 'billing_cycle' => ['required', Rule::in(['monthly', 'yearly'])], 'start_date' => ['required', 'date'], 'auto_renew' => ['sometimes', 'boolean'], 'notes' => ['nullable', 'string', 'max:1000']];
    }
}
