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
public function index(Request $request)
    {
        $user = Auth::user();

        if (! $user->podeGerenciarEscala()) {
            abort(403, 'Apenas gestores ou RH podem ver as solicitacoes dos diaristas.');
        }

        $statusFiltro = $request->query('status', 'todos');
        $dataFiltro   = $request->query('data', Carbon::today()->toDateString());

        $statusesValidos = ['pendente', 'aprovada', 'rejeitada', 'cancelada', 'todos'];
        if (! in_array($statusFiltro, $statusesValidos, true)) {
            $statusFiltro = 'todos';
        }

        $requestsQuery = DailyRequest::with(['user', 'dailyShift', 'filial'])
            ->where('empresa_id', $user->empresa_id)
            ->when($user->filial_id, fn ($q) => $q->where('filial_id', $user->filial_id))
            ->when($dataFiltro, fn ($q) => $q->whereDate('data_diaria', $dataFiltro))
            ->orderBy('data_diaria', 'asc')
            ->orderBy('daily_shift_id', 'asc');

        if ($statusFiltro !== 'todos') {
            $requestsQuery->where('status', $statusFiltro);
        }

        $requests = $requestsQuery->get();

        $inicioMes = Carbon::now()->startOfMonth();
        $fimMes = Carbon::now()->endOfMonth();

        $userIds = $requests->pluck('user_id')->unique()->values();

        $presencasPorUsuario = RegistroPresenca::whereIn('user_id', $userIds)
            ->whereBetween('data_presenca', [$inicioMes, $fimMes])
            ->when($user->filial_id, fn ($q) => $q->where('filial_id', $user->filial_id))
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
            'statusFiltro'              => $statusFiltro,
            'dataFiltro'                => $dataFiltro,
        ]);
    }

public function store(Request $request)
    {
        $user = Auth::user();

        if (! $user->isDiarista()) {
            abort(403, 'Apenas diaristas podem criar solicitacoes de diaria.');
        }

        $validated = $request->validateWithBag('diaria', [
            'data_diaria'    => 'required|date',
            'daily_shift_id' => 'required|exists:daily_shifts,id',
            'observacoes'    => 'nullable|string',
        ]);

        $jaSolicitouNoDia = DailyRequest::where('user_id', $user->id)
            ->whereDate('data_diaria', $validated['data_diaria'])
            ->exists();

        if ($jaSolicitouNoDia) {
            return redirect()
                ->route('dashboard')
                ->withErrors(['general' => 'Voce ja solicitou uma diaria para este dia.'], 'diaria')
                ->withInput();
        }

        $inicioMes = Carbon::now()->startOfMonth();
        $fimMes = Carbon::now()->endOfMonth();

        $diasTrabalhadosMes = RegistroPresenca::where('user_id', $user->id)
            ->where('status_presenca', 'presente')
            ->whereBetween('data_presenca', [$inicioMes, $fimMes])
            ->count();

        if ($diasTrabalhadosMes >= 14) {
            return redirect()
                ->route('dashboard')
                ->withErrors(['general' => 'Limite de 14 dias trabalhados no mes atingido.'], 'diaria')
                ->withInput();
        }

        $shift = DailyShift::where('id', $validated['daily_shift_id'])
            ->where('empresa_id', $user->empresa_id)
            ->when($user->filial_id, fn ($q) => $q->where('filial_id', $user->filial_id))
            ->whereDate('data_diaria', $validated['data_diaria'])
            ->firstOrFail();

        $aprovadas = $shift->requests()
            ->where('status', 'aprovada')
            ->count();

        if ($aprovadas >= $shift->vagas_totais) {
            return redirect()
                ->route('dashboard')
                ->withErrors(['general' => 'Nao ha vagas aprovadas disponiveis para esse horario.'], 'diaria')
                ->withInput();
        }

        $filialId = $shift->filial_id ?? $user->filial_id;

        DailyRequest::create([
            'user_id'        => $user->id,
            'empresa_id'     => $user->empresa_id,
            'filial_id'      => $filialId,
            'data_diaria'    => $shift->data_diaria,
            'daily_shift_id' => $shift->id,
            'status'         => 'pendente',
            'observacoes'    => $validated['observacoes'] ?? null,
        ]);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Solicitacao de diaria criada com sucesso e enviada para aprovacao.');
    }

public function myRequests()
    {
        $user = Auth::user();

        if (! $user->isDiarista()) {
            abort(403, 'Apenas diaristas podem ver esta pagina.');
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

public function updateStatus(Request $request, int $id)
    {
        $user = Auth::user();

        if (! $user->podeGerenciarEscala()) {
            abort(403, 'Apenas gestores ou RH podem atualizar o status das solicitacoes.');
        }

        $validated = $request->validate([
            'status' => 'required|in:pendente,aprovada,rejeitada,cancelada',
        ]);

        $dailyRequest = DailyRequest::with('dailyShift')
            ->where('id', $id)
            ->where('empresa_id', $user->empresa_id)
            ->when($user->filial_id, fn ($q) => $q->where('filial_id', $user->filial_id))
            ->firstOrFail();

        if ($this->requestIsPast($dailyRequest)) {
            return back()->withErrors([
                'general' => 'Nao e possivel alterar uma solicitacao com horario ja encerrado.',
            ]);
        }

        $novoStatus = $validated['status'];

        if ($novoStatus === 'aprovada' && $dailyRequest->dailyShift) {
            $shift = $dailyRequest->dailyShift;

            $aprovadas = $shift->requests()
                ->where('status', 'aprovada')
                ->count();

            if ($dailyRequest->status !== 'aprovada' && $aprovadas >= $shift->vagas_totais) {
                return back()
                    ->withErrors(['general' => 'NÃƒÂ£o hÃƒÂ¡ vagas disponÃƒÂ­veis para aprovar esta solicitaÃƒÂ§ÃƒÂ£o.'])
                    ->withInput();
            }
        }

        $dailyRequest->status = $novoStatus;
        $dailyRequest->aprovado_por = $user->id;
        $dailyRequest->save();

        return back()->with('success', 'Status da solicitacao atualizado com sucesso.');
    }

    public function acceptAll(Request $request)
    {
        $user = Auth::user();

        if (! $user->podeGerenciarEscala()) {
            abort(403, 'Apenas gestores ou RH podem aceitar todas as solicitacoes.');
        }

        $validated = $request->validate([
            'data' => 'required|date',
        ]);

        $data = $validated['data'];

        if (Carbon::parse($data)->lt(Carbon::today())) {
            return back()->withErrors([
                'general' => 'Nao e possivel aceitar solicitacoes de dias que ja passaram.',
            ]);
        }

        $shifts = DailyShift::where('empresa_id', $user->empresa_id)
            ->when($user->filial_id, fn ($q) => $q->where('filial_id', $user->filial_id))
            ->whereDate('data_diaria', $data)
            ->get();

        $totalAprovadas = 0;
        $totalPendentes = 0;
        $pendentesIgnoradas = 0;

        foreach ($shifts as $shift) {
            if ($this->shiftIsPast($shift)) {
                $pendentesIgnoradas += DailyRequest::where('daily_shift_id', $shift->id)
                    ->where('empresa_id', $user->empresa_id)
                    ->when($user->filial_id, fn ($q) => $q->where('filial_id', $user->filial_id))
                    ->where('status', 'pendente')
                    ->count();
                continue;
            }

            $aprovadas = DailyRequest::where('daily_shift_id', $shift->id)
                ->where('empresa_id', $user->empresa_id)
                ->when($user->filial_id, fn ($q) => $q->where('filial_id', $user->filial_id))
                ->where('status', 'aprovada')
                ->count();

            $disponiveis = max(0, $shift->vagas_totais - $aprovadas);

            if ($disponiveis === 0) {
                continue;
            }

            $pendentes = DailyRequest::where('daily_shift_id', $shift->id)
                ->where('empresa_id', $user->empresa_id)
                ->when($user->filial_id, fn ($q) => $q->where('filial_id', $user->filial_id))
                ->where('status', 'pendente')
                ->orderBy('created_at')
                ->get();

            $totalPendentes += $pendentes->count();

            $selecionadas = $pendentes->take($disponiveis);

            foreach ($selecionadas as $requestItem) {
                $requestItem->status = 'aprovada';
                $requestItem->aprovado_por = $user->id;
                $requestItem->save();
            }

            $totalAprovadas += $selecionadas->count();
        }

        if ($totalPendentes === 0) {
            if ($pendentesIgnoradas > 0) {
                return back()->withErrors([
                    'general' => 'Horario ja passou. Nao e possivel aceitar solicitacoes desta data.',
                ]);
            }

            return back()->withErrors(['general' => 'Nao ha solicitacoes pendentes para esta data.']);
        }

        $restantes = max(0, $totalPendentes - $totalAprovadas);
        $mensagem = 'Solicitacoes aprovadas: ' . $totalAprovadas . '.';

        if ($restantes > 0) {
            $mensagem .= ' Restaram ' . $restantes . ' pendentes por falta de vagas.';
        }

        return back()->with('success', $mensagem);
    }

    protected function requestIsPast(DailyRequest $request): bool
    {
        if ($request->dailyShift) {
            return $this->shiftIsPast($request->dailyShift);
        }

        if ($request->data_diaria) {
            return Carbon::parse($request->data_diaria)->lt(Carbon::today());
        }

        return false;
    }

    protected function shiftIsPast(DailyShift $shift): bool
    {
        $data = $shift->data_diaria instanceof Carbon
            ? $shift->data_diaria->copy()
            : Carbon::parse($shift->data_diaria);

        $dataBase = $data->format('Y-m-d');
        $inicio = Carbon::parse($dataBase . ' ' . $shift->hora_inicio);
        $fim = Carbon::parse($dataBase . ' ' . $shift->hora_fim);

        if ($fim->lessThanOrEqualTo($inicio)) {
            $fim->addDay();
        }

        return Carbon::now()->greaterThanOrEqualTo($fim);
    }
}
