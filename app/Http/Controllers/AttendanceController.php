<?php

namespace App\Http\Controllers;

use App\Models\DailyShift;
use App\Models\RegistroPresenca;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
public function listarEscalados(Request $request)
    {
        $usuarioLogado = Auth::user();

        if (! $usuarioLogado->podeRegistrarPresenca()) {
            abort(403, 'Apenas porteiros podem acessar os escalados.');
        }

        $dataSelecionada   = $request->query('data', Carbon::today()->toDateString());
        $turnoSelecionadoId = $request->query('turno_id');

        $turnosQuery = DailyShift::with(['requests' => function ($query) use ($dataSelecionada) {
                $query->whereDate('data_diaria', $dataSelecionada)
                    ->where('status', 'aprovada')
                    ->with('user');
            }, 'filial'])
            ->whereDate('data_diaria', $dataSelecionada)
            ->orderBy('hora_inicio');

        if ($usuarioLogado->empresa_id !== null) {
            $turnosQuery->where('empresa_id', $usuarioLogado->empresa_id);
        }

        if ($usuarioLogado->filial_id !== null) {
            $turnosQuery->where('filial_id', $usuarioLogado->filial_id);
        }

        $turnos = $turnosQuery->get();

        $turnoSelecionado = $turnos->firstWhere('id', (int) $turnoSelecionadoId)
            ?? $turnos->first();

        $escalados = collect();
        $presencas = collect();

        if ($turnoSelecionado) {
            $escalados = $turnoSelecionado->requests;

            $userIds = $escalados->pluck('user_id')
                ->unique()
                ->values();

            $presencasQuery = RegistroPresenca::whereIn('user_id', $userIds)
                ->whereDate('data_presenca', $dataSelecionada);

            if ($usuarioLogado->empresa_id !== null) {
                $presencasQuery->where('empresa_id', $usuarioLogado->empresa_id);
            }

            if ($usuarioLogado->filial_id !== null) {
                $presencasQuery->where('filial_id', $usuarioLogado->filial_id);
            }

            $presencas = $presencasQuery->get()->keyBy('user_id');
        }

        return view('presenca.escalados', [
            'turnos'          => $turnos,
            'turnoSelecionado'=> $turnoSelecionado,
            'escalados'       => $escalados,
            'presencas'       => $presencas,
            'dataSelecionada' => $dataSelecionada,
            'usuarioLogado'   => $usuarioLogado,
        ]);
    }

public function registrarManual(Request $request, int $userId)
    {
        $usuarioLogado = Auth::user();

        if (! $usuarioLogado->podeRegistrarPresenca()) {
            abort(403, 'Apenas porteiros podem registrar presenca.');
        }

        $dataPresenca = $request->input('data_presenca', Carbon::today()->toDateString());
        $turnoId = $request->input('turno_id');

        $funcionarioQuery = User::where('id', $userId);

        if ($usuarioLogado->empresa_id !== null) {
            $funcionarioQuery->where('empresa_id', $usuarioLogado->empresa_id);
        }

        if ($usuarioLogado->filial_id !== null) {
            $funcionarioQuery->where('filial_id', $usuarioLogado->filial_id);
        }

        $funcionario = $funcionarioQuery->firstOrFail();

        $momento = Carbon::now();

        [$registro, $mensagem] = $this->registrarPresencaParaUsuario(
            $funcionario,
            $momento,
            $dataPresenca
        );

        $params = ['data' => $dataPresenca];
        if ($turnoId) {
            $params['turno_id'] = $turnoId;
        }

        return redirect()
            ->route('presenca.escalados', $params)
            ->with('success', "{$funcionario->name}: {$mensagem}")
            ->with('registro_atualizado', $registro->id);
    }

public function listarParaPagamento(Request $request)
    {
        $usuarioLogado = Auth::user();

        $dataInicial = $request->input('data_inicial');
        $dataFinal   = $request->input('data_final');

        $query = RegistroPresenca::query()
            ->with('funcionario')
            ->where('status_presenca', 'presente')
            ->where('status_pagamento', 'pendente');

        if ($usuarioLogado->empresa_id !== null) {
            $query->where('empresa_id', $usuarioLogado->empresa_id);
        }

        if ($usuarioLogado->filial_id !== null) {
            $query->where('filial_id', $usuarioLogado->filial_id);
        }

        if ($dataInicial) {
            $query->whereDate('data_presenca', '>=', $dataInicial);
        }

        if ($dataFinal) {
            $query->whereDate('data_presenca', '<=', $dataFinal);
        }

        $registros = $query->orderBy('data_presenca', 'asc')->get();

        return view('presenca.a_pagar', [
            'registros'   => $registros,
            'dataInicial' => $dataInicial,
            'dataFinal'   => $dataFinal,
        ]);
    }

public function marcarComoPago(Request $request, int $id)
    {
        $usuarioLogado = Auth::user();
        $registro = RegistroPresenca::findOrFail($id);

        if ($usuarioLogado->empresa_id !== null && $registro->empresa_id !== null) {
            if ($usuarioLogado->empresa_id !== $registro->empresa_id) {
                abort(403, 'VocÃª nÃ£o tem permissÃ£o para alterar registros de outra empresa.');
            }
        }

        if ($usuarioLogado->filial_id !== null && $registro->filial_id !== null) {
            if ($usuarioLogado->filial_id !== $registro->filial_id) {
                abort(403, 'Voce nao tem permissao para alterar registros de outra filial.');
            }
        }

        $valorDiaria = $request->input('valor_diaria');

        $registro->status_pagamento = 'pago';
        $registro->data_pagamento   = Carbon::now();
        if ($valorDiaria !== null) {
            $registro->valor_diaria = $valorDiaria;
        }

        $registro->save();

        return redirect()
            ->back()
            ->with('success', 'Pagamento registrado com sucesso para este funcionÃ¡rio.');
    }

protected function registrarPresencaParaUsuario(User $funcionario, Carbon $momento, string $dataPresenca): array
    {
        $registroPresenca = RegistroPresenca::where('user_id', $funcionario->id)
            ->whereDate('data_presenca', $dataPresenca)
            ->first();

        $horaAtual = $momento->format('H:i:s');
        $mensagem = '';

        if (! $registroPresenca) {
            $registroPresenca = new RegistroPresenca();
            $registroPresenca->user_id           = $funcionario->id;
            $registroPresenca->empresa_id        = $funcionario->empresa_id;
            $registroPresenca->filial_id         = $funcionario->filial_id;
            $registroPresenca->data_presenca     = $dataPresenca;
            $registroPresenca->hora_entrada      = $horaAtual;
            $registroPresenca->hora_saida        = null;
            $registroPresenca->horas_trabalhadas = null;
            $registroPresenca->status_presenca   = 'presente';
            $registroPresenca->status_pagamento  = 'pendente';

            $mensagem = 'Entrada registrada com sucesso.';
        } elseif (is_null($registroPresenca->hora_entrada)) {
            $registroPresenca->hora_entrada    = $horaAtual;
            $registroPresenca->status_presenca = 'presente';
            $mensagem = 'Entrada registrada com sucesso.';
        } elseif (is_null($registroPresenca->hora_saida)) {
            $registroPresenca->hora_saida      = $horaAtual;
            $registroPresenca->status_presenca = 'presente';

            $horaEntrada = $registroPresenca->hora_entrada ?: $horaAtual;
            $horaSaida   = $registroPresenca->hora_saida;

            $entradaDateTime = Carbon::parse($dataPresenca)->setTimeFromTimeString($horaEntrada);
            $saidaDateTime   = Carbon::parse($dataPresenca)->setTimeFromTimeString($horaSaida);

            if ($saidaDateTime->lt($entradaDateTime)) {
                $saidaDateTime->addDay();
            }

            $minutos = $entradaDateTime->diffInMinutes($saidaDateTime, false);

            if ($minutos > 0) {
                $registroPresenca->horas_trabalhadas = round($minutos / 60, 2);
            }

            $mensagem = 'SaÃ­da registrada com sucesso.';
        } else {
            $mensagem = 'Entrada e saÃ­da jÃ¡ foram registradas para esta data.';
        }

        $registroPresenca->save();

        return [$registroPresenca, $mensagem];
    }
}

