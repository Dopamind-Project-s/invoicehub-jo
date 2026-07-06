<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Company;
use App\Models\SubscriptionRequest;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProvisionSubscriptionRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() === true;
    }

    public function rules(): array
    {
        $subscriptionRequest = $this->route('subscriptionRequest');

        return [
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'tax_number' => ['required', 'string', 'max:50', Rule::unique(Company::class, 'tax_number')],
            'national_number' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', Rule::in(['active', 'suspended'])],
            'jofotara_source_id' => ['nullable', 'string', 'max:50'],
            'jofotara_client_id' => ['nullable', 'string'],
            'jofotara_secret_key' => ['nullable', 'string'],
            'default_language' => ['required', Rule::in(['ar', 'en'])],
            'default_currency' => ['required', 'string', 'size:3'],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'owner_phone' => ['nullable', 'string', 'max:50'],
            'plan_id' => ['required', 'integer', Rule::exists('plans', 'id')->where('is_active', true)],
            'billing_cycle' => ['required', Rule::in(['monthly', 'yearly'])],
            'feature_keys' => ['sometimes', 'array'],
            'feature_keys.*' => ['integer', 'exists:feature_keys,id'],
            'admin_notes' => ['nullable', 'string', 'max:4000'],
            'request_id' => ['required', Rule::in([(string) ($subscriptionRequest instanceof SubscriptionRequest ? $subscriptionRequest->id : '')])],
        ];
    }
}
