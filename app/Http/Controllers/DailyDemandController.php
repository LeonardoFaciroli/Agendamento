<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyDemand;

class DailyDemandController extends Controller
{
    public function store(Request $request)
    {
        $user = auth()->user();

        if (! $user->podeGerenciarEscala()) {
            abort(403, 'Apenas gestores ou RH podem definir demanda.');
        }

        $dados = $request->validate([
            'data_diaria' => 'required|date',
            'qtd_funcionarios' => 'required|integer|min:1',
        ]);

        DailyDemand::updateOrCreate(
            ['data_diaria' => $dados['data_diaria']],
            ['qtd_funcionarios' => $dados['qtd_funcionarios']]
        );

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()
            ->route('dashboard')
            ->with('success', 'Demanda do dia salva com sucesso.');
    }
}
