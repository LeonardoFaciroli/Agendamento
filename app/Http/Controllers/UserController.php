<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Exibe o perfil do funcionário com o QR Code de presença.
     */
    public function show(int $id)
    {
        // Usuário logado
        $usuarioLogado = Auth::user();

        // Funcionário cujo perfil/QR queremos ver
        $funcionario = User::findOrFail($id);

        /**
         * Regra de acesso:
         *  - empresa ou gerente podem ver de qualquer funcionário
         *  - o próprio usuário pode ver o próprio QR
         */
        $podeVerQr =
            ($usuarioLogado->isEmpresa()  ?? false) ||
            ($usuarioLogado->isGerente()  ?? false) ||
            $usuarioLogado->id === $funcionario->id;

        if (! $podeVerQr) {
            abort(403, 'Você não tem permissão para ver o QR Code deste funcionário.');
        }

        // Gera qr_token se ainda não existir
        if (is_null($funcionario->qr_token)) {
            $funcionario->qr_token = Str::uuid()->toString();
            $funcionario->save();
        }

        return view('funcionarios.show', [
            'funcionario' => $funcionario,
        ]);
    }
}
