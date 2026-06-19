<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Company;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('admin.dashboard.index', [
            'totalCompanies' => Company::count(),
            'activeCompanies' => Company::where('status', 'active')->where('is_active', true)->count(),
            'suspendedCompanies' => Company::where('status', 'suspended')->orWhere('is_active', false)->count(),
            'jofotaraCompanies' => Company::whereNotNull('jofotara_source_id')->count(),
            'invoicesSubmitted' => 0,
            'recentAudits' => AuditLog::latest()->limit(8)->get(),
            'recentCompanies' => Company::latest()->limit(8)->get(),
        ]);
    }
}
