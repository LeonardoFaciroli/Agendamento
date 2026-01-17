<?php
namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\User;
use App\Models\Gestor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginAndRegister()
    {
        return view('auth.login');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => 'required|email|unique:users',
            'password'         => 'required|min:4|confirmed',
            'empresa_nome'     => 'required|string|max:255',
        ]);

        $empresa = Empresa::firstOrCreate(
            ['nome' => $validated['empresa_nome']],
            ['nome' => $validated['empresa_nome']]
        );

        $user = User::create([
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'password'   => Hash::make($validated['password']),
            'role'       => 'gestor',
            'empresa_id' => $empresa->id,
        ]);

        Gestor::create([
            'user_id' => $user->id,
            'empresa_id' => $empresa->id,
            'filial_id' => null,
        ]);

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if (Auth::user()->isAdmin()) {
                return redirect()->route('admin.empresas.index');
            }

            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'Credenciais inválidas.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('auth.show');
    }
}

