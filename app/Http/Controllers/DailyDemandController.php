<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyDemand;

class DailyDemandController extends Controller
{
    public function store(Request $request)
    {
        $user = auth()->user();

        // Aqui você pode ajustar a regra de permissão:
        // Por enquanto, só "empresa" pode definir demanda.
        if ($user->role !== 'empresa') {
            abort(403, 'Apenas usuários com cargo de empresa podem definir demanda.');
        }

        $dados = $request->validate([
            'data_diaria' => 'required|date',
            'qtd_funcionarios' => 'required|integer|min:1',
        ]);

        // Cria ou atualiza a demanda daquele dia
        DailyDemand::updateOrCreate(
            ['data_diaria' => $dados['data_diaria']],
            ['qtd_funcionarios' => $dados['qtd_funcionarios']]
        );

        // Se vier via AJAX, devolve JSON
        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()
            ->route('dashboard')
            ->with('success', 'Demanda do dia salva com sucesso.');
    }
}
