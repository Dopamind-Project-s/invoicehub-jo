<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('admin.dashboard.index', [
            'totalCompanies' => Company::count(),
            'activeCompanies' => Company::where('status', 'active')->where('is_active', true)->count(),
            'suspendedCompanies' => Company::where('status', 'suspended')->orWhere('is_active', false)->count(),
            'userCount' => User::count(),
            'invoiceCount' => Invoice::count(),
            'productCount' => Product::count(),
            'jofotaraCompanies' => Company::whereNotNull('jofotara_source_id')->count(),
            'recentAudits' => AuditLog::latest()->limit(8)->get(),
            'recentCompanies' => Company::latest()->limit(8)->get(),
            'recentInvoices' => Invoice::with('company')->latest()->limit(8)->get(),
        ]);
    }
}
