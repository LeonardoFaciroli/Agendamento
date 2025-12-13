{{-- resources/views/daily_requests/my.blade.php --}}

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Solicitações - Controle de Diárias</title>

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <style>
        body {
            padding-top: 60px;
        }
    </style>
</head>
<body>
    {{-- Navbar igual à do dashboard --}}
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('dashboard') }}">Controle de Diárias</a>

            <div class="d-flex ms-auto align-items-center gap-3">
                @if ($user->isFuncionario())
                    <a href="{{ route('daily_requests.my') }}"
                       class="btn btn-outline-light btn-sm active">
                        Solicitações
                    </a>
                @endif

                <span class="navbar-text text-white">
                    Logado como: {{ $user->name }} ({{ $user->role }})
                </span>

                <form method="POST" action="{{ route('auth.logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">
                        Sair
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h3>Minhas Solicitações de Diária</h3>
        <p class="text-muted">
            Aqui você vê todos os dias e horários em que fez solicitação, com o status atual.
        </p>

        @if ($requests->isEmpty())
            <div class="alert alert-info">
                Você ainda não fez nenhuma solicitação de diária.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Horário</th>
                            <th>Vagas (restantes / total)</th>
                            <th>Status</th>
                            <th>Observações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($requests as $req)
                            @php
                                $data = $req->data_diaria
                                    ? \Carbon\Carbon::parse($req->data_diaria)->format('d/m/Y')
                                    : '-';

                                $horaInicio = $req->dailyShift ? substr($req->dailyShift->hora_inicio, 0, 5) : '-';
                                $horaFim    = $req->dailyShift ? substr($req->dailyShift->hora_fim, 0, 5) : '-';

                                if ($req->dailyShift) {
                                    $usadas = $req->dailyShift->requests()
                                        ->where('status', 'aprovada')
                                        ->count();
                                    $livres = max(0, $req->dailyShift->vagas_totais - $usadas);
                                    $vagasTexto = $livres . '/' . $req->dailyShift->vagas_totais;
                                } else {
                                    $vagasTexto = '-';
                                }
                            @endphp
                            <tr>
                                <td>{{ $data }}</td>
                                <td>{{ $horaInicio }} - {{ $horaFim }}</td>
                                <td>{{ $vagasTexto }}</td>
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
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
