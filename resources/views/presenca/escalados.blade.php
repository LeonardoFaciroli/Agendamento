<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presença - Hoje</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body {
            background: #f6f7fb;
        }
        .page-hero {
            background: linear-gradient(120deg, #0d6efd, #4b8dff);
            color: #fff;
            border-radius: 16px;
            padding: 1.75rem 1.5rem;
            box-shadow: 0 10px 30px rgba(13, 110, 253, 0.18);
        }
        .badge-pill {
            border-radius: 999px;
            padding: 0.35rem 0.75rem;
        }
        .diarista-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 1rem;
            background: #fff;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
        }
        .status-dot {
            width: 10px;
            height: 10px;
            display: inline-block;
            border-radius: 50%;
        }
        .status-pendente { background: #f59e0b; }
        .status-chegada { background: #0d6efd; }
        .status-saida { background: #198754; }
        .muted-box {
            border: 1px dashed #d0d7e2;
            background: #fff;
            padding: 1rem;
            border-radius: 12px;
            text-align: center;
        }
    </style>
</head>
<body class="py-4">
<div class="container">
    @php
        $dataCarbon   = \Carbon\Carbon::parse($dataSelecionada)->locale('pt_BR');
        $dataLegivel  = $dataCarbon->translatedFormat('d \\d\\e F, Y');
        $horaAtual    = \Carbon\Carbon::now()->format('H:i');
        $turnoLabel = $turnoSelecionado
            ? substr($turnoSelecionado->hora_inicio, 0, 5) . ' às ' . substr($turnoSelecionado->hora_fim, 0, 5)
            : null;

        if ($turnoSelecionado && ! $usuarioLogado->filial_id && $turnoSelecionado->filial) {
            $turnoLabel = $turnoSelecionado->filial->nome . ' - ' . $turnoLabel;
        }
    @endphp

    <div class="page-hero mb-4 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge bg-light text-dark badge-pill">Dia: {{ $dataLegivel }}</span>
                <span class="badge bg-dark badge-pill">Servidor: {{ $horaAtual }}</span>
            </div>
            <h2 class="mt-2 mb-1">Confirmar presença dos diaristas</h2>
            <p class="mb-0 text-white-50">Escolha um horário para ver os escalados e confirme chegada/saída.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-light" href="{{ route('dashboard') }}">Voltar</a>
            <form method="GET" action="{{ route('presenca.escalados', [], false) }}">
                <input type="hidden" name="data" value="{{ \Carbon\Carbon::today()->toDateString() }}">
                <button class="btn btn-light" type="submit">Hoje</button>
            </form>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form class="row g-3 align-items-end" method="GET" action="{{ route('presenca.escalados', [], false) }}">
                <div class="col-sm-4">
                    <label class="form-label">Data</label>
                    <input type="date" name="data" class="form-control" value="{{ $dataSelecionada }}">
                </div>
                <div class="col-sm-5">
                    <label class="form-label">Horário</label>
                    <select name="turno_id" class="form-select" {{ $turnos->isEmpty() ? 'disabled' : '' }}>
                        @forelse ($turnos as $turno)
                            @php
                                $label = substr($turno->hora_inicio, 0, 5) . ' às ' . substr($turno->hora_fim, 0, 5);
                                if (! $usuarioLogado->filial_id && $turno->filial) {
                                    $label = $turno->filial->nome . ' - ' . $label;
                                }
                            @endphp
                            <option value="{{ $turno->id }}" {{ $turnoSelecionado && $turnoSelecionado->id === $turno->id ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @empty
                            <option>Nenhum horário cadastrado</option>
                        @endforelse
                    </select>
                </div>
                <div class="col-sm-3">
                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                </div>
            </form>
            <div class="small text-muted mt-2">Quando um horário é selecionado, listamos apenas os diaristas aprovados nele.</div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge text-bg-light">Buscar diarista</span>
                    <small class="text-muted">A partir de 3 letras já aparece na lista.</small>
                </div>
                <div id="statusBusca" class="small text-muted">Mostrando todos</div>
            </div>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input id="buscaEscalados" type="text" class="form-control" placeholder="Digite o nome">
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (! $turnoSelecionado)
        <div class="muted-box">
            <div class="fw-semibold mb-1">Nenhum horário encontrado</div>
            <div class="text-muted">Cadastre um turno para {{ $dataLegivel }} e volte para confirmar presenças.</div>
        </div>
    @else
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <div class="text-muted small">Horário selecionado</div>
                    <div class="fw-semibold">{{ $turnoLabel }}</div>
                </div>
                <span class="badge bg-primary-subtle text-primary">
                    {{ $escalados->count() }} diarista(s) escalado(s)
                </span>
            </div>
            <div class="card-body">
                @if ($escalados->isEmpty())
                    <div class="muted-box">Nenhum diarista aprovado para este horário.</div>
                @else
                    <div class="row g-3" id="listaDiaristas">
                        @foreach ($escalados as $req)
                            @php
                                $presenca  = $presencas->get($req->user_id);
                                $entrada   = $presenca?->hora_entrada;
                                $saida     = $presenca?->hora_saida;

                                $statusTexto = 'Chegada pendente';
                                $statusClasse = 'status-pendente';

                                if ($entrada && ! $saida) {
                                    $statusTexto  = 'Chegada confirmada às ' . $entrada;
                                    $statusClasse = 'status-chegada';
                                } elseif ($entrada && $saida) {
                                    $statusTexto  = 'Saída confirmada às ' . $saida;
                                    $statusClasse = 'status-saida';
                                }
                            @endphp
                            <div class="col-12 diarista-item" data-nome="{{ \Illuminate\Support\Str::lower($req->user->name) }}">
                                <div class="diarista-card d-flex justify-content-between align-items-start flex-wrap gap-2">
                                    <div>
                                        <div class="fw-semibold">{{ $req->user->name }}</div>
                                        <div class="text-muted small">Status: {{ $statusTexto }}</div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="status-dot {{ $statusClasse }}"></span>
                                        @if (! $entrada)
                                            <form method="POST" action="{{ route('presenca.escalados.registrar', $req->user_id, false) }}">
                                                @csrf
                                                <input type="hidden" name="data_presenca" value="{{ $dataSelecionada }}">
                                                <input type="hidden" name="turno_id" value="{{ $turnoSelecionado->id }}">
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    Confirmar chegada
                                                </button>
                                            </form>
                                        @elseif (! $saida)
                                            <form method="POST" action="{{ route('presenca.escalados.registrar', $req->user_id, false) }}">
                                                @csrf
                                                <input type="hidden" name="data_presenca" value="{{ $dataSelecionada }}">
                                                <input type="hidden" name="turno_id" value="{{ $turnoSelecionado->id }}">
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    Confirmar saída
                                                </button>
                                            </form>
                                        @else
                                            <span class="badge text-bg-success">Presença finalizada</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const inputBusca = document.getElementById('buscaEscalados');
    const statusBusca = document.getElementById('statusBusca');
    const itens = Array.from(document.querySelectorAll('.diarista-item'));

    function normalizar(str) {
        return (str || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\\u0300-\\u036f]/g, '')
            .trim();
    }

    function filtrar() {
        const termo = normalizar(inputBusca.value);

        if (! termo) {
            itens.forEach(el => el.style.display = '');
            statusBusca.textContent = 'Mostrando todos';
            return;
        }

        if (termo.length < 3) {
            itens.forEach(el => el.style.display = '');
            statusBusca.textContent = 'Digite ao menos 3 letras para filtrar.';
            return;
        }

        let visiveis = 0;
        itens.forEach(el => {
            const nome = normalizar(el.dataset.nome);
            const match = nome.includes(termo);
            el.style.display = match ? '' : 'none';
            if (match) visiveis++;
        });

        statusBusca.textContent = visiveis > 0
            ? `Filtrando: ${visiveis} resultado(s)`
            : 'Nenhum diarista com esse nome.';
    }

    if (inputBusca) {
        inputBusca.addEventListener('input', filtrar);
    }
</script>
</body>
</html>
