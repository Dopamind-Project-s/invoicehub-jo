<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->must_change_password
            && ! $request->routeIs('profile.edit', 'profile.password.update', 'logout', 'verification.*', 'password.confirm')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'يجب تغيير كلمة المرور المؤقتة قبل المتابعة.',
                    'code' => 'password_change_required',
                ], 409);
            }

            return redirect()->route('profile.edit')->with('warning', 'يجب تغيير كلمة المرور المؤقتة قبل المتابعة.');
        }

        return $next($request);
    }
}
