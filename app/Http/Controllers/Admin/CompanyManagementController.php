<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\FeatureKey;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;
use App\Services\Audit\AuditLogger;
use App\Services\Company\CompanyRoleSeeder;
use App\Services\Subscriptions\SubscriptionAccessService;
use App\Services\Subscriptions\SubscriptionEventLogger;
use App\Services\Subscriptions\SubscriptionPresentationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CompanyManagementController extends Controller
{
    public function __construct(private readonly AuditLogger $audit, private readonly CompanyRoleSeeder $roles, private readonly SubscriptionAccessService $subscriptions, private readonly SubscriptionEventLogger $subscriptionEvents, private readonly SubscriptionPresentationService $subscriptionPresenter) {}

    public function index()
    {
        return view('admin.companies.index', [
            'companies' => Company::withCount('featureKeys')->latest()->paginate(15),
        ]);
    }

    public function create()
    {
        return view('admin.companies.create', [
            'company' => new Company(['status' => 'active', 'is_active' => true, 'default_language' => 'ar', 'default_currency' => 'JOD', 'country_code' => 'JO', 'icv_prefix' => 'INV']),
            'features' => FeatureKey::where('is_active', true)->orderBy('category')->orderBy('code')->get(),
            'plans' => Plan::where('is_active', true)->with('featureKeys')->orderBy('name')->get(),
            'enabledFeatureIds' => [],
            'selectedPlanId' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $features = $data['feature_keys'] ?? [];
        $planId = $data['plan_id'] ?? null;
        unset($data['feature_keys'], $data['plan_id']);

        $company = Company::create($this->payload($data, $request));
        $this->syncPlanAndFeatures($company, $planId ? (int) $planId : null, $features);
        $this->roles->seed($company);
        $this->audit->record('admin.company.created', $company, [], $this->auditSnapshot($company), $request);
        $this->audit->record('admin.company.features.synced', $company, [], ['feature_key_ids' => $features], $request);

        return redirect()->route('admin.companies.show', $company)->with('success', 'تم إنشاء المنشأة.');
    }

    public function show(Company $company)
    {
        $company->load(['featureKeys', 'activeSubscription.plan.featureKeys', 'subscriptions.plan.featureKeys'])
            ->loadCount(['featureKeys', 'users', 'invoices'])
            ->loadCount(['invoices as submitted_invoices_count' => fn ($query) => $query->whereNotNull('submitted_at')]);
        $subscriptionAccess = $this->subscriptions->resolve($company);
        $summary = [
            'products_count' => Product::where('company_id', $company->id)->count(),
            'contacts_count' => DB::table('contacts')->where('company_id', $company->id)->count(),
            'customers_count' => DB::table('contacts')->where('company_id', $company->id)->count(),
            'active_users_count' => $company->users()->where(fn ($query) => $query->whereNull('status')->orWhere('status', 'active'))->count(),
            'last_jofotara_submission' => $company->invoices()->whereNotNull('submitted_at')->latest('submitted_at')->value('submitted_at'),
        ];

        return view('admin.companies.show', compact('company', 'subscriptionAccess', 'summary'));
    }

    public function edit(Company $company)
    {
        return view('admin.companies.edit', [
            'company' => $company,
        ]);
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $before = $this->auditSnapshot($company->fresh());
        $data = $this->validated($request, $company);
        $hasSubscriptionPayload = array_key_exists('feature_keys', $data) || array_key_exists('plan_id', $data);
        $features = $data['feature_keys'] ?? [];
        $planId = $data['plan_id'] ?? null;
        unset($data['feature_keys'], $data['plan_id']);

        $company->update($this->payload($data, $request, $company));
        $oldFeatures = $company->featureKeys()->pluck('feature_keys.id')->all();
        $oldPlanId = $company->activeSubscription?->plan_id;
        if ($hasSubscriptionPayload) {
            $this->syncPlanAndFeatures($company, $planId ? (int) $planId : null, $features, $oldPlanId ? (int) $oldPlanId : null);
        }
        $company->refresh();

        $this->audit->record('admin.company.updated', $company, $before, $this->auditSnapshot($company), $request);
        if ($hasSubscriptionPayload && $oldFeatures !== array_map('intval', $features)) {
            $this->audit->record('admin.company.features.synced', $company, ['feature_key_ids' => $oldFeatures], ['feature_key_ids' => $features], $request);
        }

        return redirect()->route('admin.companies.show', $company)->with('success', 'تم تحديث المنشأة.');
    }


    public function subscriptions(Company $company)
    {
        $company->load(['subscriptions.plan.featureKeys']);
        $subscriptionAccess = $this->subscriptions->resolve($company);
        $plans = Plan::where('is_active', true)->with('featureKeys')->orderBy('sort_order')->orderBy('name')->get();
        $history = $company->subscriptions()->with('plan')->latest('current_period_start_at')->latest('id')->paginate(15);
        $events = \App\Models\SubscriptionEvent::with('actor')->where('company_id', $company->id)->latest('occurred_at')->latest('id')->limit(30)->get();
        $timeline = $this->subscriptionPresenter->timeline($subscriptionAccess['subscription'], $subscriptionAccess['effective_status']);
        $health = $this->subscriptionPresenter->health($subscriptionAccess['subscription'], $subscriptionAccess['effective_status']);
        $renewalSummary = $this->subscriptionPresenter->renewalSummary($subscriptionAccess['subscription']);
        $paymentMethods = $this->subscriptionPresenter->paymentMethods();
        $changePreview = $this->subscriptionPresenter->changePreview($subscriptionAccess['subscription'], $plans->firstWhere('id', '!=', $subscriptionAccess['plan']?->id));

        return view('admin.companies.subscriptions.index', compact('company', 'subscriptionAccess', 'plans', 'history', 'events', 'timeline', 'health', 'renewalSummary', 'paymentMethods', 'changePreview'));
    }

    public function toggleAutoRenew(Request $request, Company $company): RedirectResponse
    {
        $subscription = $this->subscriptions->currentSubscription($company);
        abort_unless($subscription, 404);
        $before = ['auto_renew' => $subscription->auto_renew];
        $subscription->forceFill(['auto_renew' => ! $subscription->auto_renew])->save();
        $event = $subscription->auto_renew ? 'auto_renew_enabled' : 'auto_renew_disabled';
        $this->subscriptionEvents->record($company, $subscription, $event, 'admin', $request->user(), ['auto_renew' => $subscription->auto_renew]);
        $this->audit->record('admin.subscription.auto_renew_toggled', $subscription, $before, ['auto_renew' => $subscription->auto_renew], $request);
        return back()->with('success', 'تم تحديث التجديد التلقائي.');
    }

    public function cancelSubscription(Request $request, Company $company): RedirectResponse
    {
        $subscription = $this->subscriptions->currentSubscription($company);
        abort_unless($subscription, 404);
        $subscription->forceFill(['status' => 'cancelled', 'cancelled_at' => now(), 'ended_at' => now(), 'auto_renew' => false, 'status_reason' => 'admin_cancelled'])->save();
        $this->subscriptionEvents->record($company, $subscription, 'cancelled', 'admin', $request->user());
        $this->audit->record('admin.subscription.cancelled', $subscription, [], ['status' => 'cancelled'], $request);
        return back()->with('success', 'تم إلغاء الاشتراك.');
    }

    public function reactivateSubscription(Request $request, Company $company): RedirectResponse
    {
        $subscription = $this->subscriptions->currentSubscription($company);
        abort_unless($subscription, 404);
        $end = max(now(), $subscription->current_period_end_at ?: now())->addMonth();
        $subscription->forceFill(['status' => 'active', 'cancelled_at' => null, 'ended_at' => null, 'current_period_start_at' => now(), 'current_period_end_at' => $end, 'expires_at' => $end, 'grace_ends_at' => $end->copy()->addDays((int) ($subscription->plan?->grace_period_days ?? 7)), 'source' => 'admin', 'renewal_source' => 'admin'])->save();
        $this->subscriptionEvents->record($company, $subscription, 'reactivated', 'admin', $request->user());
        $this->audit->record('admin.subscription.reactivated', $subscription, [], ['status' => 'active'], $request);
        return back()->with('success', 'تمت إعادة تفعيل الاشتراك.');
    }

    public function renewSubscription(Request $request, Company $company, string $cycle): RedirectResponse
    {
        abort_unless(in_array($cycle, ['monthly', 'yearly'], true), 404);

        $subscription = $this->subscriptions->currentSubscription($company);
        abort_unless($subscription, 404, 'لا يوجد اشتراك لتجديده.');

        $before = $subscription->only(['billing_cycle', 'current_period_start_at', 'current_period_end_at', 'expires_at', 'grace_ends_at', 'status', 'renewed_at', 'source']);
        $subscription = $this->subscriptions->renew($subscription, $cycle);
        $subscription->forceFill(['renewal_source' => 'admin', 'renewed_by' => $request->user()?->id, 'payment_status' => 'not_required'])->save();
        $this->subscriptionEvents->record($company, $subscription, 'renewed', 'admin', $request->user(), ['billing_cycle' => $cycle]);

        $this->audit->record('admin.subscription.renewed', $subscription, $before, $subscription->only(['billing_cycle', 'current_period_start_at', 'current_period_end_at', 'expires_at', 'grace_ends_at', 'status', 'renewed_at', 'source']), $request);

        return back()->with('success', $cycle === 'yearly' ? 'تم تجديد الاشتراك سنوياً.' : 'تم تجديد الاشتراك شهرياً.');
    }

    public function activate(Request $request, Company $company): RedirectResponse
    {
        $before = $this->auditSnapshot($company);
        $company->forceFill(['status' => 'active', 'is_active' => true])->save();
        $this->audit->record('admin.company.activated', $company, $before, $this->auditSnapshot($company), $request);

        return back()->with('success', 'تم تفعيل المنشأة.');
    }

    public function suspend(Request $request, Company $company): RedirectResponse
    {
        $before = $this->auditSnapshot($company);
        $company->forceFill(['status' => 'suspended', 'is_active' => false])->save();
        $this->audit->record('admin.company.suspended', $company, $before, $this->auditSnapshot($company), $request);

        return back()->with('success', 'تم تعطيل المنشأة.');
    }

    private function validated(Request $request, ?Company $company = null): array
    {
        return $request->validate([
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'tax_number' => ['required', 'string', 'max:50', Rule::unique('companies', 'tax_number')->ignore($company)],
            'national_number' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', Rule::in(['active', 'suspended'])],
            'logo' => ['nullable', 'image', 'max:2048'],
            'jofotara_source_id' => ['nullable', 'string', 'max:50'],
            'jofotara_client_id' => ['nullable', 'string'],
            'jofotara_secret_key' => ['nullable', 'string'],
            'default_language' => ['required', Rule::in(['ar', 'en'])],
            'default_currency' => ['required', 'string', 'size:3'],
            'plan_id' => ['sometimes', 'nullable', 'integer', 'exists:plans,id'],
            'feature_keys' => ['sometimes', 'array'],
            'feature_keys.*' => ['integer', 'exists:feature_keys,id'],
        ]);
    }

    private function syncPlanAndFeatures(Company $company, ?int $planId, array $manualFeatureIds, ?int $oldPlanId = null): void
    {
        $featureIds = array_map('intval', $manualFeatureIds);

        if ($oldPlanId && $oldPlanId !== $planId) {
            $oldPlanFeatureIds = Plan::find($oldPlanId)?->featureKeys()->pluck('feature_keys.id')->map(fn ($id) => (int) $id)->all() ?? [];
            $featureIds = array_values(array_diff($featureIds, $oldPlanFeatureIds));
        }

        if ($planId) {
            $plan = Plan::with('featureKeys')->findOrFail($planId);
            Subscription::where('company_id', $company->id)->where('status', 'active')->where('plan_id', '!=', $planId)->update(['status' => 'cancelled', 'expires_at' => now()]);
            $subscriptionStart = now();
            $subscriptionEnd = $subscriptionStart->copy()->addYear();
            Subscription::updateOrCreate(
                ['company_id' => $company->id, 'status' => 'active'],
                [
                    'plan_id' => $plan->id,
                    'starts_at' => $subscriptionStart,
                    'expires_at' => $subscriptionEnd,
                    'billing_cycle' => 'manual',
                    'current_period_start_at' => $subscriptionStart,
                    'current_period_end_at' => $subscriptionEnd,
                    'grace_ends_at' => $subscriptionEnd->copy()->addDays((int) ($plan->grace_period_days ?? 7)),
                    'source' => 'admin',
                    'price_amount' => $plan->yearly_price ?: $plan->monthly_price,
                    'currency' => $plan->currency ?: 'JOD',
                ]
            );

            $featureIds = array_values(array_unique(array_merge($featureIds, $plan->featureKeys->pluck('id')->map(fn ($id) => (int) $id)->all())));
        }

        $company->featureKeys()->sync($featureIds);
    }

    private function payload(array $data, Request $request, ?Company $company = null): array
    {
        $data['legal_name_ar'] = $data['name_ar'];
        $data['legal_name_en'] = $data['name_en'] ?? null;
        $data['is_active'] = $data['status'] === 'active';
        $data['country_code'] = $company?->country_code ?: 'JO';
        $data['icv_prefix'] = $company?->icv_prefix ?: 'INV';

        foreach (['jofotara_client_id', 'jofotara_secret_key'] as $credential) {
            if (blank($data[$credential] ?? null)) {
                unset($data[$credential]);
            }
            if ($company && ! array_key_exists($credential, $data)) {
                $data[$credential] = $company->{$credential};
            }
        }

        if ($request->hasFile('logo')) {
            if ($company?->logo_path) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('company-logos', 'public');
        }

        return $data;
    }

    private function auditSnapshot(Company $company): array
    {
        return $company->only([
            'name_ar', 'name_en', 'legal_name_ar', 'legal_name_en', 'tax_number', 'national_number', 'phone', 'email', 'status', 'logo_path', 'jofotara_source_id', 'jofotara_client_id', 'jofotara_secret_key', 'default_language', 'default_currency', 'is_active',
        ]);
    }
}
