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
            setPermissionsTeamId($companyId);
        }

        return $next($request);
    }
}
