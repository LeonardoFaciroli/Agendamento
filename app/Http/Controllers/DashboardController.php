<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DailyShift;
use App\Models\DailyRequest;
use App\Models\Filial;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $filiais = null;

        if ($user->podeGerenciarEscala()) {
            $filiais = Filial::where('empresa_id', $user->empresa_id)
                ->orderBy('nome')
                ->get();
        }

        return view('dashboard', [
            'user' => $user,
            'filiais' => $filiais,
        ]);
    }

    public function getEvents(Request $request)
    {
        $user = Auth::user();

        if (! $request->ajax()) {
            return redirect()->route('dashboard');
        }
        $empresaId = $user->empresa_id;

        $shiftsQuery = DailyShift::with(['filial', 'createdBy'])
            ->where('empresa_id', $empresaId);

        if ($user->filial_id) {
            $shiftsQuery->where('filial_id', $user->filial_id);
        }

        $shifts = $shiftsQuery->get();

        $events = [];

        foreach ($shifts as $shift) {
            $usadas = DailyRequest::where('daily_shift_id', $shift->id)
                ->where('status', 'aprovada')
                ->count();

            $livres = max(0, $shift->vagas_totais - $usadas);

            if ($shift->data_diaria instanceof \Carbon\Carbon) {
                $data = $shift->data_diaria->format('Y-m-d');
            } else {
                $data = $shift->data_diaria;
            }

            $prefixoFilial = '';
            if (! $user->filial_id && $shift->filial) {
                $prefixoFilial = $shift->filial->nome . ' - ';
            }

            $titulo = $prefixoFilial
                    . substr($shift->hora_inicio, 0, 5)
                    . ' - '
                    . substr($shift->hora_fim, 0, 5)
                    . ' vagas: ' . $livres . '/' . $shift->vagas_totais;

            $events[] = [
                'title' => $titulo,

                'start' => $data,
                'allDay' => true,

                'backgroundColor' => '#198754',
                'borderColor'     => '#198754',
                'textColor'       => '#ffffff',

                'extendedProps' => [
                    'shift_id'        => $shift->id,
                    'filial_id'       => $shift->filial_id,
                    'data_diaria'     => $data,
                    'hora_inicio'     => $shift->hora_inicio,
                    'hora_fim'        => $shift->hora_fim,
                    'vagas_totais'    => $shift->vagas_totais,
                    'vagas_restantes' => $livres,
                    'created_by_name' => $shift->createdBy?->name,
                ],
            ];
        }

        return response()->json($events);
    }
}
