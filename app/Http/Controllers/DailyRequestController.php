<?php

namespace App\Http\Controllers;

use App\Models\DailyRequest;
use App\Models\DailyShift;
use App\Models\RegistroPresenca;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DailyRequestController extends Controller
{
    /**
     * Lista todas as solicitações de diárias da EMPRESA (gerente).
     * Rota sugerida: GET /daily-requests  -> name: daily_requests.index
     */
    public function index()
    {
        $user = Auth::user();

        if (! ($user->isEmpresa() || $user->isGerente())) {
            abort(403, 'Apenas empresas ou gerentes podem ver as solicitações dos funcionários.');
        }

        // Carrega usuário e turno junto
        $requests = DailyRequest::with(['user', 'dailyShift'])
            ->where('empresa_id', $user->empresa_id)
            ->orderBy('data_diaria', 'asc')
            ->orderBy('daily_shift_id', 'asc')
            ->get();

        $inicioMes = Carbon::now()->startOfMonth();
        $fimMes = Carbon::now()->endOfMonth();

        $userIds = $requests->pluck('user_id')->unique()->values();

        $presencasPorUsuario = RegistroPresenca::whereIn('user_id', $userIds)
            ->whereBetween('data_presenca', [$inicioMes, $fimMes])
            ->get()
            ->groupBy('user_id');

        $diasTrabalhadosPorUsuario = [];
        $pagamentosPorUsuario = [];

        foreach ($presencasPorUsuario as $funcionarioId => $presencas) {
            $dias = $presencas
                ->where('status_presenca', 'presente')
                ->pluck('data_presenca')
                ->map(fn ($data) => Carbon::parse($data)->format('d/m'))
                ->unique()
                ->values()
                ->all();

            $diasTrabalhadosPorUsuario[$funcionarioId] = $dias;

            $pagamentosPorUsuario[$funcionarioId] = [
                'pendentes'    => $presencas->where('status_pagamento', 'pendente')->count(),
                'pagos'        => $presencas->where('status_pagamento', 'pago')->count(),
                'ultimo_valor' => optional($presencas->sortByDesc('data_pagamento')->first())->valor_diaria,
            ];
        }

        return view('daily_requests.index', [
            'user'                      => $user,
            'requests'                  => $requests,
            'diasTrabalhadosPorUsuario' => $diasTrabalhadosPorUsuario,
            'pagamentosPorUsuario'      => $pagamentosPorUsuario,
            'limiteDiasMes'             => 14,
        ]);
    }

    /**
     * Funcionário cria uma requisição de diária, escolhendo um turno.
     * Rota: POST /daily-requests -> name: daily_requests.store
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (! $user->isFuncionario()) {
            abort(403, 'Apenas funcionários podem criar requisições de diária.');
        }

        $validated = $request->validate([
            'data_diaria'    => 'required|date',
            'daily_shift_id' => 'required|exists:daily_shifts,id',
            'observacoes'    => 'nullable|string',
        ]);

        // Bloqueio por dias trabalhados no mês vigente
        $inicioMes = Carbon::now()->startOfMonth();
        $fimMes = Carbon::now()->endOfMonth();

        $diasTrabalhadosMes = RegistroPresenca::where('user_id', $user->id)
            ->where('status_presenca', 'presente')
            ->whereBetween('data_presenca', [$inicioMes, $fimMes])
            ->count();

        if ($diasTrabalhadosMes >= 14) {
            return back()
                ->withErrors(['general' => 'Limite de 14 dias trabalhados no mês atingido.'])
                ->withInput();
        }

        // Garante que o turno existe para a MESMA empresa e MESMO dia
        $shift = DailyShift::where('id', $validated['daily_shift_id'])
            ->where('empresa_id', $user->empresa_id)
            ->whereDate('data_diaria', $validated['data_diaria'])
            ->firstOrFail();

        // Verifica vagas disponíveis (apenas aprovadas contam)
        $aprovadas = $shift->requests()
            ->where('status', 'aprovada')
            ->count();

        if ($aprovadas >= $shift->vagas_totais) {
            return back()
                ->withErrors(['general' => 'Não há vagas aprovadas disponíveis para esse horário.'])
                ->withInput();
        }

        DailyRequest::create([
            'user_id'        => $user->id,
            'empresa_id'     => $user->empresa_id,
            'data_diaria'    => $shift->data_diaria,        // garante mesma data do turno
            'daily_shift_id' => $shift->id,
            'status'         => 'pendente',
            'observacoes'    => $validated['observacoes'] ?? null,
        ]);

        return back()->with('success', 'Solicitação de diária criada com sucesso e enviada para aprovação.');
    }

    /**
     * Funcionário vê apenas as solicitações dele.
     * Rota: GET /minhas-solicitacoes -> name: daily_requests.my
     */
    public function myRequests()
    {
        $user = Auth::user();

        if (! $user->isFuncionario()) {
            abort(403, 'Apenas funcionários podem ver esta página.');
        }

        $requests = DailyRequest::with('dailyShift')
            ->where('user_id', $user->id)
            ->orderBy('data_diaria', 'asc')
            ->orderBy('daily_shift_id', 'asc')
            ->get();

        return view('daily_requests.my', [
            'user'     => $user,
            'requests' => $requests,
        ]);
    }

    /**
     * Empresa altera o status de uma solicitação (aprovar, rejeitar, cancelar).
     * Exemplo de rota:
     *   POST /daily-requests/{id}/status  -> name: daily_requests.updateStatus
     *   campos esperados: status = 'aprovada'|'rejeitada'|'cancelada'|'pendente'
     */
    public function updateStatus(Request $request, int $id)
    {
        $user = Auth::user();

        if (! ($user->isEmpresa() || $user->isGerente())) {
            abort(403, 'Apenas empresas ou gerentes podem atualizar o status das solicitações.');
        }

        $validated = $request->validate([
            'status' => 'required|in:pendente,aprovada,rejeitada,cancelada',
        ]);

        $dailyRequest = DailyRequest::with('dailyShift')
            ->where('id', $id)
            ->where('empresa_id', $user->empresa_id)
            ->firstOrFail();

        $novoStatus = $validated['status'];

        // Se for aprovar, checa novamente as vagas do turno
        if ($novoStatus === 'aprovada' && $dailyRequest->dailyShift) {
            $shift = $dailyRequest->dailyShift;

            // Conta quantas solicitações já estão aprovadas para esse turno
            $aprovadas = $shift->requests()
                ->where('status', 'aprovada')
                ->count();

            // Se esta requisição ainda não estava aprovada, vamos somá-la
            if ($dailyRequest->status !== 'aprovada' && $aprovadas >= $shift->vagas_totais) {
                return back()
                    ->withErrors(['general' => 'Não há vagas disponíveis para aprovar esta solicitação.'])
                    ->withInput();
            }
        }

        $dailyRequest->status = $novoStatus;
        $dailyRequest->aprovado_por = $user->id;
        $dailyRequest->save();

        return back()->with('success', 'Status da solicitação atualizado com sucesso.');
    }
}
