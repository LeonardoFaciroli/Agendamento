<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DailyShift;
use App\Models\DailyRequest;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return view('dashboard', [
            'user' => $user,
        ]);
    }

    public function getEvents(Request $request)
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        // Busca todos os turnos (horários) da empresa
        $shifts = DailyShift::where('empresa_id', $empresaId)->get();

        $events = [];

        foreach ($shifts as $shift) {
            // Vagas consumidas apenas por solicitações aprovadas
            $usadas = DailyRequest::where('daily_shift_id', $shift->id)
                ->where('status', 'aprovada')
                ->count();

            $livres = max(0, $shift->vagas_totais - $usadas);

            // Data no formato YYYY-MM-DD
            if ($shift->data_diaria instanceof \Carbon\Carbon) {
                $data = $shift->data_diaria->format('Y-m-d');
            } else {
                $data = $shift->data_diaria;
            }

            // Título: "23:45 - 08:15 vagas: 20/20"
            $titulo = substr($shift->hora_inicio, 0, 5)
                    . ' - '
                    . substr($shift->hora_fim, 0, 5)
                    . ' vagas: ' . $livres . '/' . $shift->vagas_totais;

            $events[] = [
                'title' => $titulo,

                // IMPORTANTE: usamos só a data e marcamos como allDay
                'start' => $data,
                'allDay' => true,

                // Deixa todos verdes
                'backgroundColor' => '#198754',
                'borderColor'     => '#198754',
                'textColor'       => '#ffffff',

                'extendedProps' => [
                    'shift_id'        => $shift->id,
                    'data_diaria'     => $data,
                    'hora_inicio'     => $shift->hora_inicio,
                    'hora_fim'        => $shift->hora_fim,
                    'vagas_totais'    => $shift->vagas_totais,
                    'vagas_restantes' => $livres,
                ],
            ];
        }

        return response()->json($events);
    }
}
