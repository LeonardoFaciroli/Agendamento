<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyDemand;
use App\Models\DailyRequest;

class CalendarController extends Controller
{
    public function events(Request $request)
    {
        $demands = DailyDemand::all();

        $events = [];

        foreach ($demands as $demanda) {
            // Quantas solicitações já foram APROVADAS para este dia
            $aprovadas = DailyRequest::where('data_diaria', $demanda->data_diaria)
                ->where('status', 'aprovada')
                ->count();

            $vagas = max($demanda->qtd_funcionarios - $aprovadas, 0);

            $cor = $vagas > 0 ? '#198754' : '#dc3545'; // verde se ainda tem vaga, vermelho se acabou

            $events[] = [
                'title' => 'Vagas: ' . $vagas . '/' . $demanda->qtd_funcionarios,
                'start' => $demanda->data_diaria->format('Y-m-d'),
                'allDay' => true,
                'backgroundColor' => $cor,
                'borderColor' => $cor,
                'extendedProps' => [
                    'vagas' => $vagas,
                    'total' => $demanda->qtd_funcionarios,
                    'aprovadas' => $aprovadas,
                ],
            ];
        }

        return response()->json($events);
    }
}

