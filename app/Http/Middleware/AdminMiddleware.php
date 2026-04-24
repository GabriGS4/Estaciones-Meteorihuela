<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('admin.login');
        }

        if (!$user->is_admin) {
            if ($user->sponsor_id) {
                return redirect()->route('admin.mi-colaborador.show');
            }
            Auth::logout();
            return redirect()->route('admin.login')
                ->withErrors(['email' => 'No tienes acceso al panel.']);
        }

        return $next($request);
    }
}
