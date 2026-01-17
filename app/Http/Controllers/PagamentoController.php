<?php

namespace App\Http\Controllers;

use App\Models\Pagamento;
use App\Models\RegistroPresenca;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PagamentoController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (! $user->isDiarista()) {
            abort(403, 'Apenas diaristas podem acessar os pagamentos.');
        }

        $pagamentos = Pagamento::with('registrosPresenca')
            ->where('user_id', $user->id)
            ->orderByDesc('data_pagamento')
            ->get();

        return view('pagamentos.index', [
            'user' => $user,
            'pagamentos' => $pagamentos,
        ]);
    }

    public function pendentes(Request $request)
    {
        $user = Auth::user();

        if (! $user->isGestor()) {
            abort(403, 'Apenas gestores podem listar pagamentos pendentes.');
        }

        $dataInicial = $request->input('data_inicial');
        $dataFinal = $request->input('data_final');

        $query = RegistroPresenca::with(['funcionario.diarista'])
            ->where('status_presenca', 'presente')
            ->where('status_pagamento', 'pendente')
            ->whereNotNull('hora_entrada')
            ->whereNotNull('hora_saida');

        if ($user->empresa_id !== null) {
            $query->where('empresa_id', $user->empresa_id);
        }

        if ($user->filial_id !== null) {
            $query->where('filial_id', $user->filial_id);
        }

        if ($dataInicial) {
            $query->whereDate('data_presenca', '>=', $dataInicial);
        }

        if ($dataFinal) {
            $query->whereDate('data_presenca', '<=', $dataFinal);
        }

        $registros = $query->orderBy('data_presenca')->get();

        $agrupado = $registros->groupBy('user_id')->map(function ($items) {
            $funcionario = $items->first()->funcionario;
            if (! $funcionario) {
                return null;
            }

            $perfil = $funcionario->diarista;

            return [
                'user_id' => $funcionario->id,
                'nome' => $perfil->nome ?? $funcionario->name,
                'cpf' => $perfil->cpf ?? null,
                'pix' => $perfil->pix ?? null,
                'dias_pendentes' => $items->count(),
            ];
        })->filter()->values();

        return response()->json([
            'data' => $agrupado,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (! $user->isGestor()) {
            abort(403, 'Apenas gestores podem registrar pagamentos.');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'dias_pagos' => 'required|integer|min:1',
            'data_inicial' => 'nullable|date',
            'data_final' => 'nullable|date',
            'comprovante' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $dataInicial = $validated['data_inicial'] ?? null;
        $dataFinal = $validated['data_final'] ?? null;

        $query = RegistroPresenca::where('user_id', $validated['user_id'])
            ->where('status_presenca', 'presente')
            ->where('status_pagamento', 'pendente')
            ->whereNotNull('hora_entrada')
            ->whereNotNull('hora_saida');

        if ($user->empresa_id !== null) {
            $query->where('empresa_id', $user->empresa_id);
        }

        if ($user->filial_id !== null) {
            $query->where('filial_id', $user->filial_id);
        }

        if ($dataInicial) {
            $query->whereDate('data_presenca', '>=', $dataInicial);
        }

        if ($dataFinal) {
            $query->whereDate('data_presenca', '<=', $dataFinal);
        }

        $pendentes = $query->orderBy('data_presenca')->get();
        $diasPendentes = $pendentes->count();

        if ($diasPendentes === 0) {
            return back()->withErrors([
                'general' => 'Nenhum dia pendente para pagamento neste periodo.',
            ]);
        }

        if ($validated['dias_pagos'] > $diasPendentes) {
            return back()->withErrors([
                'general' => 'Dias a pagar maior do que os dias pendentes.',
            ]);
        }

        $comprovantePath = null;
        if ($request->hasFile('comprovante')) {
            $comprovantePath = $request->file('comprovante')->store('comprovantes');
        }

        $pagamento = Pagamento::create([
            'user_id' => $validated['user_id'],
            'empresa_id' => $user->empresa_id,
            'filial_id' => $user->filial_id,
            'dias_pagos' => $validated['dias_pagos'],
            'comprovante_path' => $comprovantePath,
            'data_pagamento' => Carbon::now(),
            'pago_por' => $user->id,
        ]);

        $registrosPagos = $pendentes->take($validated['dias_pagos']);
        $dataPagamento = Carbon::now();

        foreach ($registrosPagos as $registro) {
            $registro->status_pagamento = 'pago';
            $registro->data_pagamento = $dataPagamento;
            $registro->pagamento_id = $pagamento->id;
            $registro->save();
        }

        return back()->with('success', 'Pagamento registrado. Dias pagos: ' . $validated['dias_pagos'] . '.');
    }

    public function comprovante(Pagamento $pagamento)
    {
        $user = Auth::user();

        if (! $pagamento->comprovante_path) {
            abort(404);
        }

        $isFuncionario = $user->isDiarista() && $pagamento->user_id === $user->id;

        $isGestor = $user->isGestor()
            && ($user->empresa_id === null || $user->empresa_id === $pagamento->empresa_id)
            && ($user->filial_id === null || $user->filial_id === $pagamento->filial_id);

        if (! $isFuncionario && ! $isGestor && ! $user->isAdmin()) {
            abort(403);
        }

        if (! Storage::exists($pagamento->comprovante_path)) {
            abort(404);
        }

        return response()->file(Storage::path($pagamento->comprovante_path));
    }
}
