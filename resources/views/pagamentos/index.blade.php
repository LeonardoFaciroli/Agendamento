<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamentos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('dashboard') }}">Controle de Diarias</a>
        <div class="d-flex ms-auto">
            <span class="navbar-text text-white me-3">
                Logado como: {{ $user->name }} ({{ $user->role }})
            </span>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-light btn-sm">Voltar</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    <h3 class="mb-3">Pagamentos</h3>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
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

    <div class="card">
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Data do pagamento</th>
                        <th>Dias pagos</th>
                        <th>Periodo</th>
                        <th>Comprovante</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pagamentos as $pagamento)
                        @php
                            $datas = $pagamento->registrosPresenca
                                ->pluck('data_presenca')
                                ->filter()
                                ->sort()
                                ->values();
                            $inicio = $datas->first();
                            $fim = $datas->last();
                            $periodo = $inicio
                                ? \Carbon\Carbon::parse($inicio)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($fim)->format('d/m/Y')
                                : '-';
                        @endphp
                        <tr>
                            <td>{{ optional($pagamento->data_pagamento)->format('d/m/Y H:i') }}</td>
                            <td>{{ $pagamento->dias_pagos }}</td>
                            <td>{{ $periodo }}</td>
                            <td>
                                @if ($pagamento->comprovante_path)
                                    <a href="{{ route('pagamentos.comprovante', $pagamento->id) }}" target="_blank">
                                        Ver comprovante
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center p-3">Nenhum pagamento registrado.</td>
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
