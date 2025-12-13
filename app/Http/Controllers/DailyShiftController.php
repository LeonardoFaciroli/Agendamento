<?php

namespace App\Http\Controllers;

use App\Models\DailyShift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DailyShiftController extends Controller
{
    /**
     * Retorna, em JSON, os horários (turnos) de um dia da empresa do usuário logado.
     */
    public function getByDate(string $date)
    {
        $user = Auth::user();

        $shifts = DailyShift::where('empresa_id', $user->empresa_id)
            ->whereDate('data_diaria', $date)
            ->orderBy('hora_inicio')
            ->get();

        $data = $shifts->map(function (DailyShift $shift) {
            // Apenas solicitações aprovadas consomem vagas
            $usadas = $shift->requests()
                ->where('status', 'aprovada')
                ->count();

            return [
                'id'              => $shift->id,
                'hora_inicio'     => $shift->hora_inicio,
                'hora_fim'        => $shift->hora_fim,
                'vagas_totais'    => $shift->vagas_totais,
                'vagas_restantes' => max(0, $shift->vagas_totais - $usadas),
            ];
        });

        return response()->json($data);
    }

    /**
     * Empresa (gerente) cria um horário (turno) para um dia.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->isEmpresa()) {
            abort(403, 'Apenas empresas podem criar horários de diária.');
        }

        $validated = $request->validate([
            'data_diaria'  => 'required|date',
            'hora_inicio'  => 'required',
            'hora_fim'     => 'required',
            'vagas_totais' => 'required|integer|min:1',
        ]);

        DailyShift::create([
            'empresa_id'   => $user->empresa_id,
            'data_diaria'  => $validated['data_diaria'],
            'hora_inicio'  => $validated['hora_inicio'],
            'hora_fim'     => $validated['hora_fim'],
            'vagas_totais' => $validated['vagas_totais'],
        ]);

        return response()->json([
            'message' => 'Horário criado com sucesso.',
        ]);
    }
}
