<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showForm()
    {
        if (Auth::check() && Auth::user()->isEmployee()) {
            return redirect()->route('empleados.dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        $login = $request->input('login');

        // Determinar si es email o nombre de usuario
        if (str_contains($login, '@')) {
            $user = \App\Models\User::where('email', $login)->first();
        } else {
            $user = \App\Models\User::whereRaw('LOWER(name) = ?', [mb_strtolower($login)])->first();
        }

        if (! $user) {
            return back()->withErrors(['login' => 'Usuario o contraseña incorrectos.'])->onlyInput('login');
        }

        if (! Auth::attempt(['email' => $user->email, 'password' => $request->input('password')], $request->boolean('remember'))) {
            return back()->withErrors(['login' => 'Usuario o contraseña incorrectos.'])->onlyInput('login');
        }

        $user = Auth::user();

        if (! $user->isEmployee()) {
            Auth::logout();
            return back()->withErrors(['login' => 'No tienes permiso para acceder al panel.']);
        }

        if (! $user->active) {
            Auth::logout();
            return back()->withErrors(['login' => 'Tu cuenta ha sido desactivada.']);
        }

        $request->session()->regenerate();
        $request->session()->put('caja_prompt', true);
        return redirect()->route('empleados.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('empleados.login');
    }
}
