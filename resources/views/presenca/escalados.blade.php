{{-- resources/views/presenca/escalados.blade.php --}}

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escalados do Dia</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #f9fafb 0%, #eef2f6 100%);
            min-height: 100vh;
        }
        .page-header {
            background: #0d6efd;
            color: #fff;
            padding: 1.5rem 1.25rem;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(13, 110, 253, 0.15);
        }
        .timeline {
            position: relative;
            padding-left: 18px;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 6px;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(180deg, rgba(13,110,253,0.6), rgba(13,110,253,0.1));
            border-radius: 999px;
        }
        .shift-card {
            position: relative;
            background: #fff;
            border-radius: 12px;
            padding: 1rem 1.1rem 1.1rem;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
            border: 1px solid #e9ecef;
        }
        .shift-card::before {
            content: '';
            position: absolute;
            left: -9px;
            top: 18px;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            border: 3px solid #fff;
            background: #0d6efd;
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.15);
        }
        .person-chip {
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 0.75rem;
            margin-bottom: 0.6rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }
        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            display: inline-block;
        }
        .status-open { background: #ffc107; }
        .status-in { background: #0d6efd; }
        .status-out { background: #198754; }
        .empty-state {
            text-align: center;
            color: #6c757d;
            padding: 2.5rem 1rem;
            border: 1px dashed #cbd3da;
            border-radius: 12px;
            background: #fff;
        }
        .btn-ghost {
            background: #fff;
            border: 1px solid #e9ecef;
        }
    </style>
</head>
<body class="py-4">
<div class="container">
    @php
        $dataCarbon = \Carbon\Carbon::parse($dataSelecionada)->locale('pt_BR');
        $dataLegivel = $dataCarbon->translatedFormat('d \\d\\e F, Y');
        $timezone = config('app.timezone', 'UTC');
    @endphp

    <div class="page-header mb-4 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark">Dia vigente</span>
                <span class="badge bg-dark">TZ {{ $timezone }}</span>
            </div>
            <h2 class="mt-2 mb-0">{{ ucfirst($dataLegivel) }}</h2>
            <p class="mb-0">Escalados por horário com registro manual de presença.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-ghost" href="{{ route('dashboard') }}">Voltar</a>
            <a class="btn btn-outline-light" href="{{ route('presenca.scanner') }}">
                Abrir leitor de QR
            </a>
        </div>
    </div>

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <form method="GET" action="{{ route('presenca.escalados') }}" class="d-flex align-items-center gap-2 flex-wrap">
            <label class="form-label mb-0">Data:</label>
            <input type="date" name="data" value="{{ $dataSelecionada }}" class="form-control" style="width: 200px;">
            <button type="submit" class="btn btn-primary">Ver escalados</button>
        </form>

        <div class="text-end">
            <div class="small text-muted">Hora atual (servidor): {{ \Carbon\Carbon::now()->format('H:i:s') }}</div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge text-bg-light">Buscar funcionário</span>
                <small class="text-muted">Digite pelo menos 2 letras para filtrar.</small>
            </div>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input id="buscaEscalados" type="text" class="form-control" placeholder="Nome do funcionário">
            </div>
            <div id="statusBusca" class="small text-muted mt-1">Mostrando todos</div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($turnos->isEmpty())
        <div class="empty-state">
            <h5>Sem turnos cadastrados para este dia</h5>
            <p class="mb-0">Adicione um turno no calendário para ver quem foi escalado.</p>
        </div>
    @else
        <div class="timeline">
            <div class="row gy-3">
                @foreach ($turnos as $turno)
                    @php
                        $horaInicio = substr($turno->hora_inicio, 0, 5);
                        $horaFim    = substr($turno->hora_fim, 0, 5);
                        $escalados  = $turno->requests;
                    @endphp
                    <div class="col-12">
                        <div class="shift-card">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                <div>
                                    <div class="text-uppercase text-muted small">Turno</div>
                                    <div class="h5 mb-0">{{ $horaInicio }} - {{ $horaFim }}</div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary-subtle text-primary">
                                        {{ $escalados->count() }} escalado(s)
                                    </span>
                                </div>
                            </div>

                            @if ($escalados->isEmpty())
                                <p class="mb-0 text-muted">Nenhum funcionário aprovado para este horário.</p>
                            @else
                                @foreach ($escalados as $req)
                                    @php
                                        $presenca  = $presencas->get($req->user_id);
                                        $entrada   = $presenca?->hora_entrada;
                                        $saida     = $presenca?->hora_saida;

                                        if (! $entrada) {
                                            $statusLabel = 'Aguardando entrada';
                                            $statusClass = 'status-open';
                                        } elseif ($entrada && ! $saida) {
                                            $statusLabel = 'Entrada: ' . $entrada;
                                            $statusClass = 'status-in';
                                        } else {
                                            $statusLabel = 'Entrada: ' . $entrada . ' · Saída: ' . $saida;
                                            $statusClass = 'status-out';
                                        }
                                    @endphp
                                    <div class="person-chip" data-nome="{{ strtolower($req->user->name) }}">
                                        <div class="d-flex flex-column">
                                            <div class="fw-semibold">{{ $req->user->name }}</div>
                                            <div class="text-muted small">Status: {{ $statusLabel }}</div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="status-dot {{ $statusClass }}"></span>
                                            @if (! $entrada)
                                                <form method="POST" action="{{ route('presenca.escalados.registrar', $req->user_id) }}">
                                                    @csrf
                                                    <input type="hidden" name="data_presenca" value="{{ $dataSelecionada }}">
                                                    <button type="submit" class="btn btn-sm btn-primary">
                                                        Registrar entrada
                                                    </button>
                                                </form>
                                            @elseif ($entrada && ! $saida)
                                                <form method="POST" action="{{ route('presenca.escalados.registrar', $req->user_id) }}">
                                                    @csrf
                                                    <input type="hidden" name="data_presenca" value="{{ $dataSelecionada }}">
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        Registrar saída
                                                    </button>
                                                </form>
                                            @else
                                                <span class="badge bg-success">Completo</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const inputBusca = document.getElementById('buscaEscalados');
    const chips = Array.from(document.querySelectorAll('.person-chip'));
    const statusBusca = document.getElementById('statusBusca');

    function normalizar(str) {
        return str
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
    }

    function filtrar() {
        const termo = normalizar(inputBusca.value);

        if (termo.length === 0) {
            chips.forEach(c => c.style.display = '');
            statusBusca.textContent = 'Mostrando todos';
            return;
        }

        if (termo.length < 2) {
            chips.forEach(c => c.style.display = '');
            statusBusca.textContent = 'Digite pelo menos 2 letras para filtrar.';
            return;
        }

        let visiveis = 0;
        chips.forEach(c => {
            const nome = normalizar(c.dataset.nome || '');
            const match = nome.includes(termo);
            c.style.display = match ? '' : 'none';
            if (match) visiveis++;
        });

        statusBusca.textContent = visiveis > 0
            ? `Filtrando: ${visiveis} resultado(s)`
            : 'Nenhum funcionário com esse nome.';
    }

    if (inputBusca) {
        inputBusca.addEventListener('input', filtrar);
    }
</script>
</body>
</html>
