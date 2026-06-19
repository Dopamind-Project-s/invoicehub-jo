<?php

declare(strict_types=1);

namespace Spatie\Permission\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();
        $companyId = $request->route('company')?->id ?? $user?->company_id;
        abort_unless($user?->canInCompany($permission, $companyId), 403, 'Permission denied.');

        return $next($request);
    }
}
