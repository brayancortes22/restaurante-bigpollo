<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectUserByRole(Auth::user()->role);
        }

        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Esta cuenta de usuario se encuentra desactivada. Contacte al administrador.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            $targetRoute = match ($user->role) {
                'cocina' => '/kds',
                'cajero' => '/pos',
                'admin', 'superadmin' => '/admin/menu',
                default => '/waiter',
            };

            return redirect()->intended($targetRoute)->with('success', '¡Bienvenido a Big Pollo, ' . $user->name . '!');
        }

        return back()->withErrors([
            'email' => 'Credenciales inválidas. Verifique su correo y contraseña.',
        ])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('info', 'Sesión cerrada correctamente.');
    }

    private function redirectUserByRole(string $role): RedirectResponse
    {
        return match ($role) {
            'cocina' => redirect('/kds'),
            'cajero' => redirect('/pos'),
            'admin', 'superadmin' => redirect('/admin/menu'),
            default => redirect('/waiter'),
        };
    }
}
