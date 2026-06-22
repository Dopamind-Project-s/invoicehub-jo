<?php

declare(strict_types=1);

namespace App\Services\CompanyWorkspace;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class CompanyDashboardStatsService
{
    public const TTL_SECONDS = 600;

    public function get(Company $company): array
    {
        return Cache::remember(self::key($company), self::TTL_SECONDS, fn (): array => $this->build($company));
    }

    public static function forget(Company|int $company): void
    {
        Cache::forget(self::key($company));
    }

    public static function key(Company|int $company): string
    {
        $companyId = $company instanceof Company ? $company->id : $company;

        return 'company-dashboard-stats:company:'.$companyId;
    }

    private function build(Company $company): array
    {
        $invoiceQuery = Invoice::query()->where('company_id', $company->id);

        return [
            'product_count' => Product::where('company_id', $company->id)->count(),
            'contact_count' => Contact::where('company_id', $company->id)->count(),
            'invoice_count' => (clone $invoiceQuery)->count(),
            'draft_invoices' => (clone $invoiceQuery)->where('status', Invoice::STATUS_DRAFT)->count(),
            'ready_invoices' => (clone $invoiceQuery)->where('status', Invoice::STATUS_READY)->count(),
            'submitted_invoices' => (clone $invoiceQuery)->where('status', Invoice::STATUS_SUBMITTED)->count(),
            'jofotara_error_invoices' => (clone $invoiceQuery)->where('jofotara_status', 'ERROR')->count(),
            'sales_total' => (float) (clone $invoiceQuery)->whereNotIn('status', [Invoice::STATUS_CANCELLED])->sum('grand_total'),
            'tax_total' => (float) (clone $invoiceQuery)->whereNotIn('status', [Invoice::STATUS_CANCELLED])->sum('tax_total'),
            'recent_invoices' => (clone $invoiceQuery)->with('contact')->latest()->limit(5)->get(),
            'recent_activities' => AuditLog::query()
                ->where(function ($query) use ($company): void {
                    $query->where(fn ($inner) => $inner->where('auditable_type', Company::class)->where('auditable_id', $company->id))
                        ->orWhereHas('user', fn ($user) => $user->where('company_id', $company->id));
                })
                ->latest()
                ->limit(5)
                ->get(),
            'last_submitted_invoice' => (clone $invoiceQuery)->whereNotNull('jofotara_submitted_at')->latest('jofotara_submitted_at')->first(),
            'last_activity' => AuditLog::query()
                ->where(function ($query) use ($company): void {
                    $query->where(fn ($inner) => $inner->where('auditable_type', Company::class)->where('auditable_id', $company->id))
                        ->orWhereHas('user', fn ($user) => $user->where('company_id', $company->id));
                })
                ->latest()
                ->first(),
        ];
    }
}
