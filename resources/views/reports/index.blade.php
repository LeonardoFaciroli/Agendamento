<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('dashboard') }}">Controle de Diárias</a>
        <div class="d-flex ms-auto">
            <span class="navbar-text text-white me-3">
                Logado como: {{ $user->name }} ({{ $user->role }})
            </span>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-light btn-sm">Voltar</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Relatórios de Vagas</h3>
        <small class="text-muted">Período: {{ \Carbon\Carbon::parse($inicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fim)->format('d/m/Y') }}</small>
    </div>

    <form method="GET" class="row g-2 mb-4">
        <div class="col-12 col-md-3">
            <label class="form-label">Período</label>
            <select name="periodo" class="form-select">
                <option value="dia" {{ $periodo === 'dia' ? 'selected' : '' }}>Dia</option>
                <option value="semana" {{ $periodo === 'semana' ? 'selected' : '' }}>Semana</option>
                <option value="mes" {{ $periodo === 'mes' ? 'selected' : '' }}>Mês</option>
            </select>
        </div>
        <div class="col-12 col-md-3">
            <label class="form-label">Data base</label>
            <input type="date" name="data" value="{{ $dataBase }}" class="form-control">
        </div>
        <div class="col-12 col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Aplicar filtro</button>
        </div>
    </form>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Pagamentos pendentes (PDF)</h5>
            <p class="text-muted mb-3">Gere um PDF com funcionários que ainda não foram pagos em um intervalo de datas.</p>
            <form method="GET" action="{{ route('reports.pendentes.pdf') }}" class="row g-2">
                <div class="col-12 col-md-3">
                    <label class="form-label">Data inicial</label>
                    <input type="date" name="data_inicial" value="{{ $inicio }}" class="form-control">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Data final</label>
                    <input type="date" name="data_final" value="{{ $fim }}" class="form-control">
                </div>
                <div class="col-12 col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-danger w-100">
                        Gerar PDF pendentes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12 col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Vagas totais</h5>
                    <p class="display-6 mb-0">{{ $totalVagas }}</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Vagas ocupadas (aprovadas)</h5>
                    <p class="display-6 mb-0">{{ $ocupadas }}</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Vagas livres</h5>
                    <p class="display-6 mb-0">{{ max(0, $totalVagas - $ocupadas) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Detalhes por dia</div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Vagas lançadas</th>
                        <th>Ocupadas (aprovadas)</th>
                        <th>Livres</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($porDia as $dia)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($dia['data'])->format('d/m/Y') }}</td>
                            <td>{{ $dia['vagas'] }}</td>
                            <td>{{ $dia['aprovadas'] }}</td>
                            <td>{{ max(0, $dia['vagas'] - $dia['aprovadas']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center p-3">Nenhum dado para o período selecionado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
