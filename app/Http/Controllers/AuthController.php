<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // 1. Validar los datos del formulario (el campo del formulario se llama 'email')
        $credenciales = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'Escribe tu correo electrónico.',
            'email.email'       => 'El correo no tiene un formato válido.',
            'password.required' => 'Escribe tu contraseña.',
        ]);

        // 2. Límite de intentos: máximo 5 intentos fallidos por minuto (evita que adivinen contraseñas)
        $llave = Str::lower($request->input('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($llave, 5)) {
            $segundos = RateLimiter::availableIn($llave);

            return back()
                ->withErrors(['email' => "Demasiados intentos. Espera {$segundos} segundos e inténtalo de nuevo."])
                ->onlyInput('email');
        }

        // 3. Intentar autenticar (con la casilla "Recordarme")
        if (! Auth::attempt($credenciales, $request->boolean('remember'))) {
            RateLimiter::hit($llave, 60); // cuenta el intento fallido por 60 segundos

            return back()
                ->withErrors(['email' => 'El correo o la contraseña son incorrectos.'])
                ->onlyInput('email');
        }

        RateLimiter::clear($llave); // login correcto: se reinicia el contador

        $user = Auth::user();

        // 4. Validar que el usuario esté activo
        if (! $user->activo) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['email' => 'Tu cuenta se encuentra inactiva. Contacta al Administrador.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        // 5. Redirección según el Rol del usuario
        $destino = match ($user->role->nombre ?? '') {
            'Administrador General', 'Gerente de Sucursal' => 'dashboard',
            'Vendedor'                                     => 'inventario.index',
            'Cajero'                                       => 'caja.index',
            default                                        => 'dashboard',
        };

        // Si no tiene permiso para esa pantalla, lo mandamos al Panel (todos pueden verlo)
        if ($destino !== 'dashboard' && ! $user->tienePermiso($destino)) {
            $destino = 'dashboard';
        }

        return redirect()->intended(route($destino));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}