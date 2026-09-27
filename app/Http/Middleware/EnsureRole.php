<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * `role:admin,drc` — the signed-in user must be active and hold one of the roles.
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user->isActive()) {
            Auth::logout();
            $request->session()->invalidate();

            return redirect()->route('login')->withErrors(['email' => 'Your account is not active.']);
        }

        $allowed = array_map(fn (string $r) => Role::from($r), $roles);
        abort_unless($user->hasRole(...$allowed), 403);

        return $next($request);
    }
}
