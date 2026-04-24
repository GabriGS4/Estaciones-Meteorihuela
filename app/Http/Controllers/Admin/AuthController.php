<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            if (!$user->is_admin && !$user->sponsor_id) {
                Auth::logout();
                return back()
                    ->withErrors(['email' => 'No tienes acceso al panel.'])
                    ->onlyInput('email');
            }

            return $this->redirectByRole($user);
        }

        return back()->withErrors(['email' => 'Credenciales incorrectas.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }

    private function redirectByRole($user)
    {
        if ($user->is_admin) {
            return redirect()->intended(route('admin.dashboard'));
        }
        return redirect()->route('admin.mi-colaborador.show');
    }
}
