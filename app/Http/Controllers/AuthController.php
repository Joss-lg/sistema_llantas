<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login'); 
    }

    public function login(Request $request)
    {
        // 1. Validar los datos del formulario
        $request->validate([
            'usuario' => ['required', 'string'],
            'password' => ['required'],
        ]);

        // 2. Mapear 'usuario' a 'email'
        $credentials = [
            'email'    => $request->usuario,
            'password' => $request->password,
        ];

        // 3. Intentar autenticar
        if (Auth::attempt($credentials)) {

            $user = Auth::user();

            // Validar que el usuario esté activo
            if (! $user->activo) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'usuario' => 'Tu cuenta se encuentra inactiva. Contacta al Administrador.',
                ]);
            }

            $request->session()->regenerate();

            // 4. Redirección según el Rol del usuario
            return match ($user->role->nombre ?? '') {
                'Administrador General', 'Gerente de Sucursal' => redirect()->intended(route('dashboard')),
                'Vendedor'                                     => redirect()->intended(route('inventario.index')),
                'Cajero'                                       => redirect()->intended(route('ventas.index')),
                default                                        => redirect()->intended(route('ventas.index')),
            };
        }

        return back()->withErrors([
            'usuario' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}