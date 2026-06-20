<?php

namespace App\Http\Controllers\CompanyWorkspace;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\Product;

class WorkspaceDashboardController extends Controller
{
    public function __invoke(Company $company)
    {
        $company->load('featureKeys');
        return view('company.dashboard', [
            'company' => $company,
            'productCount' => Product::where('company_id', $company->id)->count(),
            'contactCount' => Contact::where('company_id', $company->id)->count(),
            'invoiceCount' => Invoice::where('company_id', $company->id)->count(),
            'pendingInvoices' => Invoice::where('company_id', $company->id)->where('status', Invoice::STATUS_PENDING)->count(),
            'approvedInvoices' => Invoice::where('company_id', $company->id)->where('status', Invoice::STATUS_APPROVED)->count(),
            'recentInvoices' => Invoice::where('company_id', $company->id)->latest()->limit(5)->get(),
        ]);
    }
}
