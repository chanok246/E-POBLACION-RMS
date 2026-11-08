<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureFieldPersonnelIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('field_personnel');
        $personnel = $request->user('field_personnel');

        if (!$personnel || !$personnel->canAccessFieldCollection()) {
            if ($guard->check()) {
                $guard->logout();
            }

            return redirect()
                ->route('field-personnel.login')
                ->withErrors([
                    'username' => 'Your Field Collection account is inactive or no longer authorized.',
                ]);
        }

        return $next($request);
    }
}
