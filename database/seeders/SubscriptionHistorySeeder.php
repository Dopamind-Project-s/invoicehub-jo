<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\Subscriptions\SubscriptionEventLogger;
use Illuminate\Database\Seeder;

class SubscriptionHistorySeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('tax_number', '9578331')->first();
        $starter = Plan::where('slug', 'starter')->first();
        if (! $company || ! $starter) return;
        $now = now();
        foreach ([
            ['demo-previous-monthly','monthly',$now->copy()->subMonths(2),$now->copy()->subMonth(),$starter->monthly_price],
            ['demo-previous-yearly','yearly',$now->copy()->subYears(2),$now->copy()->subYear(),$starter->yearly_price],
        ] as [$source,$cycle,$start,$end,$price]) {
            $subscription = Subscription::updateOrCreate(['company_id'=>$company->id,'source'=>$source], ['plan_id'=>$starter->id,'status'=>'expired','billing_cycle'=>$cycle,'starts_at'=>$start,'expires_at'=>$end,'current_period_start_at'=>$start,'current_period_end_at'=>$end,'grace_ends_at'=>$end->copy()->addDays(7),'price_amount'=>$price,'currency'=>'JOD','auto_renew'=>false,'payment_status'=>'not_required','renewal_source'=>'admin']);
            app(SubscriptionEventLogger::class)->record($company, $subscription, 'expired', 'system', null, ['occurred_at' => $end->copy()->addDays(7)]);
        }
    }
}
