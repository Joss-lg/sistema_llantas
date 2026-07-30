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

        // 2. Mapear 'usuario' a 'email' para que Auth::attempt busque en la columna correcta
        $credentials = [
            'email'    => $request->usuario,
            'password' => $request->password,
        ];

        // 3. Intentar autenticar con las credenciales adaptadas
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended('ventas'); 
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