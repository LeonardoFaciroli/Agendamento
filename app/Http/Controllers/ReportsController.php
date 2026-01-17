<?php

namespace App\Http\Controllers;

use App\Models\DailyRequest;
use App\Models\DailyShift;
use App\Models\RegistroPresenca;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (! $user->isGestor()) {
            abort(403, 'Apenas gestores podem acessar os relatorios.');
        }

        $filtro = $request->input('periodo', 'mes');
        $dataBase = $request->input('data', Carbon::today()->toDateString());
        $dataBaseCarbon = Carbon::parse($dataBase);

        if ($filtro === 'dia') {
            $inicio = $dataBaseCarbon->copy()->startOfDay();
            $fim    = $dataBaseCarbon->copy()->endOfDay();
        } elseif ($filtro === 'semana') {
            $inicio = $dataBaseCarbon->copy()->startOfWeek();
            $fim    = $dataBaseCarbon->copy()->endOfWeek();
        } else {
            $inicio = $dataBaseCarbon->copy()->startOfMonth();
            $fim    = $dataBaseCarbon->copy()->endOfMonth();
        }

        $shifts = DailyShift::where('empresa_id', $user->empresa_id)
            ->when($user->filial_id, fn ($q) => $q->where('filial_id', $user->filial_id))
            ->whereBetween('data_diaria', [$inicio->toDateString(), $fim->toDateString()])
            ->get();

        $totalVagas = $shifts->sum('vagas_totais');

        $ocupadas = DailyRequest::where('empresa_id', $user->empresa_id)
            ->when($user->filial_id, fn ($q) => $q->where('filial_id', $user->filial_id))
            ->where('status', 'aprovada')
            ->whereBetween('data_diaria', [$inicio->toDateString(), $fim->toDateString()])
            ->count();

        $porDia = $shifts->groupBy('data_diaria')->map(function ($lista) use ($user) {
            $data = $lista->first()->data_diaria instanceof Carbon
                ? $lista->first()->data_diaria->toDateString()
                : $lista->first()->data_diaria;

            $vagas = $lista->sum('vagas_totais');

            $aprovadasDia = DailyRequest::where('empresa_id', $user->empresa_id)
                ->when($user->filial_id, fn ($q) => $q->where('filial_id', $user->filial_id))
                ->where('status', 'aprovada')
                ->whereDate('data_diaria', $data)
                ->count();

            return [
                'data'       => $data,
                'vagas'      => $vagas,
                'aprovadas'  => $aprovadasDia,
            ];
        })->values();

        return view('reports.index', [
            'user'        => $user,
            'totalVagas'  => $totalVagas,
            'ocupadas'    => $ocupadas,
            'porDia'      => $porDia,
            'periodo'     => $filtro,
            'dataBase'    => $dataBaseCarbon->toDateString(),
            'inicio'      => $inicio->toDateString(),
            'fim'         => $fim->toDateString(),
        ]);
    }

public function pendentesPdf(Request $request)
    {
        $user = Auth::user();

        if (! $user->isGestor()) {
            abort(403, 'Apenas gestores podem gerar relatorios.');
        }

        $dataInicial = $request->input('data_inicial');
        $dataFinal   = $request->input('data_final');

        $query = RegistroPresenca::with('funcionario')
            ->where('status_pagamento', 'pendente');

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

        $registros = $query->orderBy('data_presenca')->orderBy('user_id')->get();

        $linhas = [];
        $periodoLabel = ($dataInicial ?: 'início') . ' - ' . ($dataFinal ?: 'hoje');
        $linhas[] = 'Pagamentos pendentes';
        $linhas[] = 'Período: ' . $periodoLabel;
        $linhas[] = 'Total de registros: ' . $registros->count();
        $linhas[] = '';
        $linhas[] = 'Nome | Data | Entrada | Saida | Horas';

        foreach ($registros as $registro) {
            $nome   = $registro->funcionario->name ?? 'N/A';
            $data   = optional($registro->data_presenca)->format('d/m/Y');
            $entrada = $registro->hora_entrada ?? '-';
            $saida   = $registro->hora_saida ?? '-';
            $horas   = $registro->horas_trabalhadas ?? '-';

            $linhas[] = sprintf(
                '%s | %s | %s | %s | %s',
                $nome,
                $data,
                $entrada,
                $saida,
                $horas
            );
        }

        $pdf = $this->montarPdfTexto($linhas);

        $fileName = 'pendentes_' . str_replace(['/', '\\'], '-', $periodoLabel) . '.pdf';

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->header('Content-Length', strlen($pdf));
    }

protected function montarPdfTexto(array $linhas): string
    {
        $escape = fn (string $text) => str_replace(
            ['\\', '(', ')'],
            ['\\\\', '\\(', '\\)'],
            $text
        );

        $y = 780;
        $conteudo = "BT\n/F1 12 Tf\n";
        foreach ($linhas as $linha) {
            $conteudo .= sprintf("1 0 0 1 40 %.2f Tm (%s) Tj\n", $y, $escape($linha));
            $y -= 16;
        }
        $conteudo .= "ET";

        $lenConteudo = strlen($conteudo);

        $pdf  = "%PDF-1.4\n";
        $pdf .= "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n";
        $pdf .= "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj\n";
        $pdf .= "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >> endobj\n";
        $pdf .= "4 0 obj << /Length {$lenConteudo} >> stream\n{$conteudo}\nendstream endobj\n";
        $pdf .= "5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj\n";

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 6\n";
        $pdf .= "0000000000 65535 f \n";

        $offsets = [];
        $offsets[] = strpos($pdf, "1 0 obj");
        $offsets[] = strpos($pdf, "2 0 obj");
        $offsets[] = strpos($pdf, "3 0 obj");
        $offsets[] = strpos($pdf, "4 0 obj");
        $offsets[] = strpos($pdf, "5 0 obj");

        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        $pdf .= "trailer << /Size 6 /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }
}
