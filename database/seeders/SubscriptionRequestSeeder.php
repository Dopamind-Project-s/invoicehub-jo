<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Plan;
use App\Models\SubscriptionChangeRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class SubscriptionRequestSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('tax_number', '9578331')->first();
        $user = User::where('email', 'company@invosync.local')->first();
        $plan = Plan::where('slug', 'business')->first();
        if (! $company || ! $user || ! $plan) return;
        SubscriptionChangeRequest::updateOrCreate(
            ['company_id'=>$company->id,'requested_by'=>$user->id,'request_type'=>'upgrade','status'=>'pending'],
            ['current_subscription_id'=>$company->subscriptions()->where('source','demo-current')->value('id'),'requested_plan_id'=>$plan->id,'billing_cycle'=>'yearly','notes'=>'طلب تجريبي للترقية إلى باقة الأعمال.']
        );
    }
}
