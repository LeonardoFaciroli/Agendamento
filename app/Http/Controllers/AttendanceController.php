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
    /**
     * Tela que abre a camera e lê o QR (empresa / gerente / porteiro).
     */
    public function scanner()
    {
        $usuarioLogado = Auth::user();

        if (! $usuarioLogado->podeRegistrarPresenca()) {
            abort(403, 'Apenas empresa, gerentes ou porteiros podem acessar o leitor de QR Code.');
        }

        return view('presenca.scanner', [
            'user' => $usuarioLogado,
        ]);
    }

    /**
     * Rota chamada quando o QRCode é escaneado.
     * A URL dentro do QR aponta para esta rota.
     */
    public function registrarViaQr(string $token)
    {
        $usuarioLogado = Auth::user();

        if (! $usuarioLogado->podeRegistrarPresenca()) {
            abort(403, 'Apenas empresa, gerentes ou porteiros podem registrar presença via QR Code.');
        }

        $funcionario = User::where('qr_token', $token)->first();

        if (! $funcionario) {
            abort(404, 'Funcionário não encontrado para este QR Code.');
        }

        if ($usuarioLogado->empresa_id !== null && $funcionario->empresa_id !== null) {
            if ($usuarioLogado->empresa_id !== $funcionario->empresa_id) {
                abort(403, 'Você não tem permissão para registrar presença de funcionários de outra empresa.');
            }
        }

        $dataHoje = Carbon::today()->toDateString();
        $agora    = Carbon::now();

        [$registroPresenca, $mensagem] = $this->registrarPresencaParaUsuario(
            $funcionario,
            $agora,
            $dataHoje
        );

        return view('presenca.confirmacao', [
            'funcionario' => $funcionario,
            'registro'    => $registroPresenca,
            'mensagem'    => $mensagem,
        ]);
    }

    /**
     * Lista escalados do dia (por turno) para registro manual de presença.
     */
    public function listarEscalados(Request $request)
    {
        $usuarioLogado = Auth::user();

        if (! $usuarioLogado->podeRegistrarPresenca()) {
            abort(403, 'Apenas empresa, gerentes ou porteiros podem acessar os escalados.');
        }

        $dataSelecionada = $request->query('data', Carbon::today()->toDateString());

        $turnosQuery = DailyShift::with(['requests' => function ($query) use ($dataSelecionada) {
                $query->whereDate('data_diaria', $dataSelecionada)
                    ->where('status', 'aprovada')
                    ->with('user');
            }])
            ->whereDate('data_diaria', $dataSelecionada)
            ->orderBy('hora_inicio');

        if ($usuarioLogado->empresa_id !== null) {
            $turnosQuery->where('empresa_id', $usuarioLogado->empresa_id);
        }

        $turnos = $turnosQuery->get();

        $userIds = $turnos->flatMap(function (DailyShift $shift) {
            return $shift->requests->pluck('user_id');
        })->unique()->values();

        $presencasQuery = RegistroPresenca::whereIn('user_id', $userIds)
            ->whereDate('data_presenca', $dataSelecionada);

        if ($usuarioLogado->empresa_id !== null) {
            $presencasQuery->where('empresa_id', $usuarioLogado->empresa_id);
        }

        $presencas = $presencasQuery->get()->keyBy('user_id');

        return view('presenca.escalados', [
            'turnos'          => $turnos,
            'presencas'       => $presencas,
            'dataSelecionada' => $dataSelecionada,
            'usuarioLogado'   => $usuarioLogado,
        ]);
    }

    /**
     * Registrar presença manualmente (entrada/saída) para um funcionário escalado.
     */
    public function registrarManual(Request $request, int $userId)
    {
        $usuarioLogado = Auth::user();

        if (! $usuarioLogado->podeRegistrarPresenca()) {
            abort(403, 'Apenas empresa, gerentes ou porteiros podem registrar presença.');
        }

        $dataPresenca = $request->input('data_presenca', Carbon::today()->toDateString());

        $funcionarioQuery = User::where('id', $userId);

        if ($usuarioLogado->empresa_id !== null) {
            $funcionarioQuery->where('empresa_id', $usuarioLogado->empresa_id);
        }

        $funcionario = $funcionarioQuery->firstOrFail();

        $momento = Carbon::now();

        [$registro, $mensagem] = $this->registrarPresencaParaUsuario(
            $funcionario,
            $momento,
            $dataPresenca
        );

        return redirect()
            ->route('presenca.escalados', ['data' => $dataPresenca])
            ->with('success', "{$funcionario->name}: {$mensagem}")
            ->with('registro_atualizado', $registro->id);
    }

    /**
     * Listar presenças com status_pagamento = pendente para serem pagas.
     */
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

    /**
     * Marcar um registro de presença como pago.
     */
    public function marcarComoPago(Request $request, int $id)
    {
        $usuarioLogado = Auth::user();
        $registro = RegistroPresenca::findOrFail($id);

        if ($usuarioLogado->empresa_id !== null && $registro->empresa_id !== null) {
            if ($usuarioLogado->empresa_id !== $registro->empresa_id) {
                abort(403, 'Você não tem permissão para alterar registros de outra empresa.');
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
            ->with('success', 'Pagamento registrado com sucesso para este funcionário.');
    }

    /**
     * Centraliza a lógica de registrar entrada/saída para um usuário e data.
     *
     * @return array{0: RegistroPresenca, 1: string}
     */
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

            // Lida com turnos que viram o dia (saída < entrada)
            if ($saidaDateTime->lt($entradaDateTime)) {
                $saidaDateTime->addDay();
            }

            $minutos = $entradaDateTime->diffInMinutes($saidaDateTime, false);

            if ($minutos > 0) {
                $registroPresenca->horas_trabalhadas = round($minutos / 60, 2);
            }

            $mensagem = 'Saída registrada com sucesso.';
        } else {
            $mensagem = 'Entrada e saída já foram registradas para esta data.';
        }

        $registroPresenca->save();

        return [$registroPresenca, $mensagem];
    }
}
