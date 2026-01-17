<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requisições de Diárias</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Requisições de Diárias dos Funcionários</h3>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">Voltar ao Dashboard</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('daily_requests.index', [], false) }}" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Data</label>
                        <input type="date" name="data" value="{{ $dataFiltro ?? \Carbon\Carbon::today()->toDateString() }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            @php
                                $opts = [
                                    'todos'     => 'Todos',
                                    'pendente'  => 'Pendentes',
                                    'aprovada'  => 'Aprovadas',
                                    'rejeitada' => 'Rejeitadas',
                                    'cancelada' => 'Canceladas',
                                ];
                            @endphp
                            @foreach ($opts as $valor => $label)
                                <option value="{{ $valor }}" {{ ($statusFiltro ?? 'todos') === $valor ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex gap-2 align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                        <a href="{{ route('daily_requests.index') }}" class="btn btn-outline-secondary">Hoje</a>
                    </div>
                </form>
                <div class="small text-muted mt-2">
                    Por padrão mostramos apenas as solicitações do dia vigente ({{ \Carbon\Carbon::today()->format('d/m/Y') }}). Use o filtro para outros dias ou status.
                </div>
                <form method="POST" action="{{ route('daily_requests.acceptAll', [], false) }}" class="d-flex justify-content-end mt-3">
                    @csrf
                    <input type="hidden" name="data" value="{{ $dataFiltro ?? \Carbon\Carbon::today()->toDateString() }}">
                    <button type="submit" class="btn btn-primary"
                            onclick="return confirm('Aceitar todas as solicitacoes pendentes desta data?');">
                        Aceitar todas
                    </button>
                </form>
            </div>
        </div>

        <table class="table table-striped table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Funcionário</th>
                    <th>Data</th>
                    <th>Horário</th>
                    <th>Status</th>
                    <th>Observações</th>
                    <th>Ações</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($requests as $req)
                    @php
                        $dataFormatada = $req->data_diaria
                            ? \Carbon\Carbon::parse($req->data_diaria)->format('d/m/Y')
                            : '-';

                        $horaInicio = $req->dailyShift ? substr($req->dailyShift->hora_inicio, 0, 5) : '-';
                        $horaFim    = $req->dailyShift ? substr($req->dailyShift->hora_fim, 0, 5) : '-';
                        $infoPagamento = $pagamentosPorUsuario[$req->user_id] ?? ['pendentes' => 0, 'pagos' => 0, 'ultimo_valor' => null];
                        $diasTrabalhados = $diasTrabalhadosPorUsuario[$req->user_id] ?? [];
                        $turnoPassou = false;

                        if ($req->dailyShift) {
                            $dataBase = \Carbon\Carbon::parse($req->dailyShift->data_diaria)->format('Y-m-d');
                            $inicioTurno = \Carbon\Carbon::parse($dataBase . ' ' . $req->dailyShift->hora_inicio);
                            $fimTurno = \Carbon\Carbon::parse($dataBase . ' ' . $req->dailyShift->hora_fim);

                            if ($fimTurno->lessThanOrEqualTo($inicioTurno)) {
                                $fimTurno->addDay();
                            }

                            $turnoPassou = \Carbon\Carbon::now()->greaterThanOrEqualTo($fimTurno);
                        } elseif ($req->data_diaria) {
                            $turnoPassou = \Carbon\Carbon::parse($req->data_diaria)->lt(\Carbon\Carbon::today());
                        }
                    @endphp
                    <tr>
                        <td>{{ $req->user->name }}</td>
                        @if (! $user->filial_id)
                            <td>{{ $req->filial->nome ?? '-' }}</td>
                        @endif
                        <td>{{ $dataFormatada }}</td>
                        <td>{{ $horaInicio }} - {{ $horaFim }}</td>
                        <td>
                            @if ($req->status === 'aprovada')
                                <span class="badge bg-success">Aprovada</span>
                            @elseif ($req->status === 'rejeitada')
                                <span class="badge bg-danger">Rejeitada</span>
                            @elseif ($req->status === 'cancelada')
                                <span class="badge bg-secondary">Cancelada</span>
                            @else
                                <span class="badge bg-warning text-dark">Pendente</span>
                            @endif
                        </td>
                        <td>{{ $req->observacoes ?? '-' }}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <form method="POST"
                                      action="{{ route('daily_requests.updateStatus', $req->id, false) }}"
                                      class="d-inline">
                                    @csrf
                                    <input type="hidden" name="status" value="aprovada">
                                    <button type="submit" class="btn btn-success btn-sm" {{ $turnoPassou ? 'disabled' : '' }}>
                                        Aprovar
                                    </button>
                                </form>
                                <form method="POST"
                                      action="{{ route('daily_requests.updateStatus', $req->id, false) }}"
                                      class="d-inline ms-1">
                                    @csrf
                                    <input type="hidden" name="status" value="rejeitada">
                                    <button type="submit" class="btn btn-danger btn-sm" {{ $turnoPassou ? 'disabled' : '' }}>
                                        Rejeitar
                                    </button>
                                </form>
                                <form method="POST"
                                      action="{{ route('daily_requests.updateStatus', $req->id, false) }}"
                                      class="d-inline ms-1">
                                    @csrf
                                    <input type="hidden" name="status" value="cancelada">
                                    <button type="submit" class="btn btn-outline-secondary btn-sm" {{ $turnoPassou ? 'disabled' : '' }}>
                                        Cancelar
                                    </button>
                                </form>
                            </div>
                            @if ($turnoPassou)
                                <div class="small text-muted mt-1">Horario encerrado.</div>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="dropdown">
                                <button class="btn btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    ⋯
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li class="dropdown-header">Pagamentos</li>
                                    <li class="px-3 small">
                                        Pendentes: {{ $infoPagamento['pendentes'] ?? 0 }}<br>
                                        Pagos: {{ $infoPagamento['pagos'] ?? 0 }}<br>
                                        Último valor: {{ $infoPagamento['ultimo_valor'] ? 'R$ ' . number_format($infoPagamento['ultimo_valor'], 2, ',', '.') : '-' }}
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li class="dropdown-header">Dias trabalhados no mês</li>
                                    <li class="px-3 small">
                                        {{ count($diasTrabalhados) }} dia(s): {{ implode(', ', $diasTrabalhados) ?: 'Nenhum' }}
                                        @if (count($diasTrabalhados) >= $limiteDiasMes)
                                            <div class="text-danger mt-1">Atingiu limite de {{ $limiteDiasMes }} dias</div>
                                        @endif
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $user->filial_id ? 7 : 8 }}" class="text-center">
                            Nenhuma requisição encontrada para esta empresa.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
