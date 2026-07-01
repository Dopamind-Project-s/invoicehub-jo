<?php

namespace App\Http\Controllers\CompanyWorkspace;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\CompanyWorkspace\CompanyDashboardStatsService;

class WorkspaceDashboardController extends Controller
{
    public function __invoke(Company $company, CompanyDashboardStatsService $stats)
    {
        $company->load(['featureKeys', 'activeSubscription.plan']);
        $dashboardStats = $stats->get($company);

        return view('company.dashboard', ['company' => $company, 'stats' => $dashboardStats] + $this->legacyAliases($dashboardStats));
    }

    private function legacyAliases(array $stats): array
    {
        return [
            'productCount' => $stats['product_count'],
            'contactCount' => $stats['contact_count'],
            'invoiceCount' => $stats['invoice_count'],
            'pendingInvoices' => $stats['ready_invoices'],
            'approvedInvoices' => $stats['submitted_invoices'],
            'jofotaraSubmittedCount' => $stats['submitted_invoices'],
            'pendingJofotaraCount' => $stats['ready_invoices'],
            'importedInvoiceCount' => 0,
            'recentInvoices' => $stats['recent_invoices'],
        ];
    }
}
