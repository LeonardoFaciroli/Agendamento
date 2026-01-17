<?php

namespace App\Http\Controllers;

use App\Models\DailyShift;
use App\Models\Filial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class DailyShiftController extends Controller
{
public function getByDate(Request $request, string $date)
    {
        $user = Auth::user();

        if (! $request->ajax()) {
            return redirect()->route('dashboard');
        }

        $shifts = DailyShift::where('empresa_id', $user->empresa_id)
            ->when($user->filial_id, function ($query) use ($user) {
                $query->where('filial_id', $user->filial_id);
            })
            ->whereDate('data_diaria', $date)
            ->orderBy('hora_inicio')
            ->get();

        $data = $shifts->map(function (DailyShift $shift) {
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

public function store(Request $request)
    {
        $user = Auth::user();

        if (! $user->podeGerenciarEscala()) {
            abort(403, 'Apenas gestores ou RH podem criar horarios de diaria.');
        }

        $validated = $request->validate([
            'data_diaria'  => 'required|date',
            'hora_inicio'  => 'required',
            'hora_fim'     => 'required',
            'vagas_totais' => 'required|integer|min:1',
            'filial_id'    => 'nullable|exists:filiais,id',
        ]);

        $filialId = $user->filial_id;

        if (! $filialId) {
            $filialId = $validated['filial_id'] ?? null;
            if (! $filialId) {
                return response()->json([
                    'message' => 'Selecione uma filial para criar o horario.',
                ], 422);
            }

            $filial = Filial::where('id', $filialId)
                ->where('empresa_id', $user->empresa_id)
                ->first();

            if (! $filial) {
                return response()->json([
                    'message' => 'Filial invalida para esta empresa.',
                ], 422);
            }
        }

        $inicioTurno = Carbon::parse($validated['data_diaria'] . ' ' . $validated['hora_inicio']);
        if ($inicioTurno->isPast()) {
            return response()->json([
                'message' => 'Horario ja passou. Escolha um horario futuro.',
            ], 422);
        }

        DailyShift::create([
            'empresa_id'   => $user->empresa_id,
            'filial_id'    => $filialId,
            'data_diaria'  => $validated['data_diaria'],
            'hora_inicio'  => $validated['hora_inicio'],
            'hora_fim'     => $validated['hora_fim'],
            'vagas_totais' => $validated['vagas_totais'],
            'created_by'   => $user->id,
        ]);

        return response()->json([
            'message' => 'Horário criado com sucesso.',
        ]);
    }

    public function update(Request $request, DailyShift $dailyShift)
    {
        $user = Auth::user();

        if (! $user->podeGerenciarEscala()) {
            abort(403, 'Apenas gestores ou RH podem atualizar horarios de diaria.');
        }

        if ($dailyShift->empresa_id !== $user->empresa_id) {
            abort(403, 'Voce nao tem permissao para editar este horario.');
        }

        if ($user->filial_id && $dailyShift->filial_id !== $user->filial_id) {
            abort(403, 'Voce nao tem permissao para editar este horario.');
        }

        $validated = $request->validate([
            'hora_inicio'  => 'required',
            'hora_fim'     => 'required',
            'vagas_totais' => 'required|integer|min:1',
        ]);

        $dataDiaria = $dailyShift->data_diaria instanceof Carbon
            ? $dailyShift->data_diaria->format('Y-m-d')
            : (string) $dailyShift->data_diaria;

        $inicioTurno = Carbon::parse($dataDiaria . ' ' . $validated['hora_inicio']);
        if ($inicioTurno->isPast()) {
            return response()->json([
                'message' => 'Horario ja passou. Escolha um horario futuro.',
            ], 422);
        }

        $dailyShift->hora_inicio = $validated['hora_inicio'];
        $dailyShift->hora_fim = $validated['hora_fim'];
        $dailyShift->vagas_totais = $validated['vagas_totais'];
        $dailyShift->updated_by = $user->id;
        $dailyShift->save();

        return response()->json([
            'message' => 'Horario atualizado com sucesso.',
        ]);
    }
}
