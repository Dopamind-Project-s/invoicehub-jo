<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionRequest;
use App\Models\User;
use App\Services\Company\CompanyRoleSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Permission\Models\Role;

use function setPermissionsTeamId;

class SubscriptionRequestProvisioningService
{
    public function __construct(private readonly CompanyRoleSeeder $roles) {}

    public function provision(SubscriptionRequest $request, array $data, User $actor): SubscriptionRequest
    {
        if ($request->company_id || $request->subscription_id || $request->status === SubscriptionRequest::STATUS_PROVISIONED) {
            throw new RuntimeException('تم تجهيز هذا الطلب مسبقًا ولا يمكن إنشاء منشأة ثانية منه.');
        }

        return DB::transaction(function () use ($request, $data, $actor): SubscriptionRequest {
            $plan = Plan::with('featureKeys')->findOrFail((int) $data['plan_id']);
            $start = now();
            $end = $data['billing_cycle'] === 'yearly' ? $start->copy()->addYear() : $start->copy()->addMonth();

            $company = Company::create([
                'name_ar' => $data['name_ar'],
                'name_en' => $data['name_en'] ?? null,
                'legal_name_ar' => $data['name_ar'],
                'legal_name_en' => $data['name_en'] ?? null,
                'tax_number' => $data['tax_number'],
                'national_number' => $data['national_number'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'status' => $data['status'],
                'is_active' => $data['status'] === 'active',
                'default_language' => $data['default_language'],
                'default_currency' => $data['default_currency'],
                'country_code' => 'JO',
                'icv_prefix' => 'INV',
                'jofotara_source_id' => $data['jofotara_source_id'] ?? null,
                'jofotara_client_id' => $data['jofotara_client_id'] ?? null,
                'jofotara_secret_key' => $data['jofotara_secret_key'] ?? null,
            ]);

            $this->roles->seed($company);
            $featureIds = array_values(array_unique(array_merge(
                array_map('intval', $data['feature_keys'] ?? []),
                $plan->featureKeys->pluck('id')->map(fn ($id) => (int) $id)->all()
            )));
            $company->featureKeys()->sync($featureIds);

            $user = User::create([
                'company_id' => $company->id,
                'name' => $data['owner_name'],
                'email' => $data['owner_email'],
                'phone' => $data['owner_phone'] ?? null,
                'status' => 'active',
                'role' => 'user',
                'password' => Hash::make(Str::password(40)),
            ]);

            $ownerRole = Role::query()->where('company_id', $company->id)->where('name', 'Owner')->first();
            if ($ownerRole) {
                setPermissionsTeamId($company->id);
                $user->syncRoles([$ownerRole->id]);
            }

            $subscription = Subscription::create([
                'company_id' => $company->id,
                'plan_id' => $plan->id,
                'starts_at' => $start,
                'expires_at' => $end,
                'status' => 'active',
                'billing_cycle' => $data['billing_cycle'],
                'current_period_start_at' => $start,
                'current_period_end_at' => $end,
                'grace_ends_at' => $end->copy()->addDays((int) ($plan->grace_period_days ?? 7)),
                'source' => 'subscription_request',
                'payment_status' => 'not_required',
                'price_amount' => $data['billing_cycle'] === 'yearly' ? $plan->yearly_price : $plan->monthly_price,
                'currency' => $plan->currency ?: 'JOD',
                'metadata' => ['subscription_request_id' => $request->id],
            ]);

            $request->forceFill([
                'status' => SubscriptionRequest::STATUS_PROVISIONED,
                'admin_notes' => $data['admin_notes'] ?? $request->admin_notes,
                'approved_at' => $request->approved_at ?: now(),
                'approved_by' => $request->approved_by ?: $actor->id,
                'company_id' => $company->id,
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'provisioned_at' => now(),
                'provisioned_by' => $actor->id,
            ])->save();

            Password::sendResetLink(['email' => $user->email]);

            return $request->refresh()->load(['company', 'user', 'subscription.plan', 'provisionedBy', 'approvedBy']);
        });
    }
}
