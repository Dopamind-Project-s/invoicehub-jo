<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use function setPermissionsTeamId;

class SetCompanyPermissionTeam
{
    public function handle(Request $request, Closure $next): Response
    {
        $company = $request->route('company');
        $companyId = $company instanceof Company ? $company->id : ($company ? (int) $company : null);

        if ($companyId !== null) {
            abort_if($request->user() && ! $request->user()->isSuperAdmin() && (int) $request->user()->company_id !== (int) $companyId, 404);
            setPermissionsTeamId($companyId);
        }

        return $next($request);
    }
}
