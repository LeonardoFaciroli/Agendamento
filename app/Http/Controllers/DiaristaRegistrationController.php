<?php

namespace App\Http\Controllers;

use App\Models\Filial;
use App\Models\Diarista;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DiaristaRegistrationController extends Controller
{
    public function create()
    {
        $filiais = Filial::with('empresa')
            ->orderBy('nome')
            ->get();

        return view('auth.diarista_register', [
            'filiais' => $filiais,
        ]);
    }

    public function store(Request $request)
    {
        $cpfDigits = preg_replace('/\D+/', '', (string) $request->input('cpf', ''));
        $request->merge(['cpf' => $cpfDigits]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'cpf' => [
                'required',
                'digits:11',
                'unique:diaristas,cpf',
                function (string $attribute, string $value, $fail) {
                    if (! $this->isValidCpf($value)) {
                        $fail('CPF invalido.');
                    }
                },
            ],
            'telefone' => 'required|string|max:30',
            'pix' => 'required|string|max:255',
            'endereco' => 'required|string|max:255',
            'cidade' => 'nullable|string|max:120',
            'funcao' => 'nullable|string|max:120',
            'filial_id' => 'required|exists:filiais,id',
            'password' => 'required|min:6|confirmed',
        ]);

        $filial = Filial::findOrFail($validated['filial_id']);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'diarista',
            'empresa_id' => $filial->empresa_id,
            'filial_id' => $filial->id,
        ]);

        Diarista::create([
            'user_id' => $user->id,
            'empresa_id' => $filial->empresa_id,
            'filial_id' => $filial->id,
            'nome' => $validated['name'],
            'cpf' => $validated['cpf'],
            'telefone' => $validated['telefone'],
            'pix' => $validated['pix'],
            'endereco' => $validated['endereco'],
            'cidade' => $validated['cidade'] ?? null,
            'funcao' => $validated['funcao'] ?? null,
        ]);

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    private function isValidCpf(string $cpf): bool
    {
        if (strlen($cpf) !== 11) {
            return false;
        }

        if (preg_match('/^(\\d)\\1{10}$/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $sum = 0;
            for ($i = 0; $i < $t; $i++) {
                $sum += (int) $cpf[$i] * (($t + 1) - $i);
            }

            $digit = (10 * $sum) % 11;
            $digit = $digit === 10 ? 0 : $digit;

            if ((int) $cpf[$t] !== $digit) {
                return false;
            }
        }

        return true;
    }
}
