<?php

namespace App\Http\Controllers;

use App\Models\Diarista;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiaristaProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();

        if (! $user->isDiarista()) {
            abort(403, 'Apenas diaristas podem acessar este perfil.');
        }

        $perfil = $user->diarista;

        return view('diaristas.profile', [
            'user' => $user,
            'perfil' => $perfil,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        if (! $user->isDiarista()) {
            abort(403, 'Apenas diaristas podem atualizar este perfil.');
        }

        $perfil = $user->diarista;

        $cpfDigits = preg_replace('/\D+/', '', (string) $request->input('cpf', ''));
        $request->merge(['cpf' => $cpfDigits]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'cpf' => [
                'required',
                'digits:11',
                'unique:diaristas,cpf,' . ($perfil?->id ?? 0) . ',id',
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
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->save();

        $perfil = $perfil ?: new Diarista(['user_id' => $user->id]);
        $perfil->empresa_id = $user->empresa_id;
        $perfil->filial_id = $user->filial_id;
        $perfil->nome = $validated['name'];
        $perfil->cpf = $validated['cpf'];
        $perfil->telefone = $validated['telefone'];
        $perfil->pix = $validated['pix'];
        $perfil->endereco = $validated['endereco'];
        $perfil->cidade = $validated['cidade'] ?? null;
        $perfil->funcao = $validated['funcao'] ?? null;
        $perfil->save();

        return back()->with('success', 'Dados atualizados com sucesso.');
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
