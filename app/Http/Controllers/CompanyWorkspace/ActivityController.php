<?php

declare(strict_types=1);

namespace App\Http\Controllers\CompanyWorkspace;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Company;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request, Company $company)
    {
        $query = AuditLog::query()->latest();
        $query->where(function ($q) use ($company): void {
            $q->where(fn ($inner) => $inner->where('auditable_type', Company::class)->where('auditable_id', $company->id))
                ->orWhereHas('user', fn ($user) => $user->where('company_id', $company->id));
        });
        if ($request->filled('action')) {
            $query->where('action', 'like', '%'.$request->string('action').'%');
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date('date'));
        }

        return view('company.activity.index', ['company' => $company, 'activities' => $query->paginate(20)->withQueryString(), 'users' => $company->users()->orderBy('name')->get()]);
    }
}
