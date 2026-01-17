<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empresa - {{ $empresa->nome }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>{{ $empresa->nome }}</h3>
            <a href="{{ route('admin.empresas.index') }}" class="btn btn-outline-primary btn-sm">Voltar</a>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Informacoes da empresa</h5>
                        <p class="mb-1"><strong>Cadastro:</strong> {{ optional($empresa->created_at)->format('d/m/Y H:i') }}</p>
                        <p class="mb-1"><strong>Status:</strong> {{ $empresa->billing_status ?? 'N/A' }}</p>
                        <p class="mb-0"><strong>Filiais:</strong> {{ $empresa->filiais->count() }}</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Equipe vinculada</h5>
                        <p class="mb-1"><strong>Diaristas:</strong> {{ $diaristasCount }}</p>
                        <p class="mb-1"><strong>Gestores:</strong> {{ $gestoresCount }}</p>
                        <p class="mb-1"><strong>RH:</strong> {{ $rhsCount }}</p>
                        <p class="mb-0"><strong>Porteiros:</strong> {{ $supervisoresCount }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Filiais</h5>
                @if ($empresa->filiais->isEmpty())
                    <p class="text-muted mb-0">Nenhuma filial cadastrada.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Cidade</th>
                                    <th>Diaristas</th>
                                    <th>Cadastro</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($empresa->filiais as $filial)
                                    <tr>
                                        <td>{{ $filial->nome }}</td>
                                        <td>{{ $filial->cidade ?? '-' }}</td>
                                        <td>{{ $diaristasPorFilial[$filial->id] ?? 0 }}</td>
                                        <td>{{ optional($filial->created_at)->format('d/m/Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
