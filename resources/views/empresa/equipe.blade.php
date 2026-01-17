<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipe - Empresa</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Equipe da empresa</h3>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-primary btn-sm">Voltar</a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Novo RH</h5>
                        <form method="POST" action="{{ route('equipe.rh.store') }}" class="row g-3">
                            @csrf
                            <div class="col-12">
                                <label class="form-label">Nome do RH</label>
                                <input type="text" name="rh_nome" class="form-control" value="{{ old('rh_nome') }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">E-mail do RH</label>
                                <input type="email" name="rh_email" class="form-control" value="{{ old('rh_email') }}" required>
                            </div>
                            @if (! $user->filial_id)
                                <div class="col-12">
                                    <label class="form-label">Pesquisar filial</label>
                                    <input type="search" id="rhFilialSearch" class="form-control" placeholder="Digite o nome da filial...">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Filial</label>
                                    <select name="rh_filial_id" id="rhFilialSelect" class="form-select" required>
                                        <option value="">Selecione uma filial</option>
                                        @foreach ($filiais as $filial)
                                            <option value="{{ $filial->id }}" {{ old('rh_filial_id') == $filial->id ? 'selected' : '' }}>
                                                {{ $filial->nome }}@if ($filial->cidade) ({{ $filial->cidade }}) @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <div class="col-12">
                                    <label class="form-label">Filial</label>
                                    <input type="text" class="form-control" value="{{ $user->filial?->nome }}" readonly>
                                </div>
                            @endif
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100">Enviar convite</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Novo porteiro</h5>
                        <form method="POST" action="{{ route('equipe.porteiros.store') }}" class="row g-3">
                            @csrf
                            <div class="col-12">
                                <label class="form-label">Nome do porteiro</label>
                                <input type="text" name="supervisor_nome" class="form-control" value="{{ old('supervisor_nome') }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">E-mail do porteiro</label>
                                <input type="email" name="supervisor_email" class="form-control" value="{{ old('supervisor_email') }}" required>
                            </div>
                            @if (! $user->filial_id)
                                <div class="col-12">
                                    <label class="form-label">Pesquisar filial</label>
                                    <input type="search" id="supervisorFilialSearch" class="form-control" placeholder="Digite o nome da filial...">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Filial</label>
                                    <select name="supervisor_filial_id" id="supervisorFilialSelect" class="form-select" required>
                                        <option value="">Selecione uma filial</option>
                                        @foreach ($filiais as $filial)
                                            <option value="{{ $filial->id }}" {{ old('supervisor_filial_id') == $filial->id ? 'selected' : '' }}>
                                                {{ $filial->nome }}@if ($filial->cidade) ({{ $filial->cidade }}) @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <div class="col-12">
                                    <label class="form-label">Filial</label>
                                    <input type="text" class="form-control" value="{{ $user->filial?->nome }}" readonly>
                                </div>
                            @endif
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100">Enviar convite</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Alterar filial do diarista</h5>
                        <form method="POST" action="{{ route('equipe.diaristas.filial') }}" class="row g-3">
                            @csrf
                            <div class="col-12">
                                <label class="form-label">Pesquisar diarista</label>
                                <input type="search" id="diaristaSearch" class="form-control" placeholder="Digite o nome do diarista...">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Diarista</label>
                                <select name="diarista_id" id="diaristaSelect" class="form-select" required>
                                    <option value="">Selecione um diarista</option>
                                    @foreach ($diaristas as $diarista)
                                        <option value="{{ $diarista->id }}" {{ old('diarista_id') == $diarista->id ? 'selected' : '' }}>
                                            {{ $diarista->nome }} - {{ $diarista->user->email ?? 'Sem e-mail' }} @if ($diarista->filial) ({{ $diarista->filial->nome }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Pesquisar filial</label>
                                <input type="search" id="diaristaFilialSearch" class="form-control" placeholder="Digite o nome da filial...">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Nova filial</label>
                                <select name="filial_id" id="diaristaFilialSelect" class="form-select" required>
                                    <option value="">Selecione uma filial</option>
                                    @foreach ($filiais as $filial)
                                        <option value="{{ $filial->id }}" {{ old('filial_id') == $filial->id ? 'selected' : '' }}>
                                            {{ $filial->nome }}@if ($filial->cidade) ({{ $filial->cidade }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100">Atualizar filial</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Convites pendentes</h5>
                        @if ($convitesPendentes->isEmpty())
                            <p class="text-muted mb-0">Nenhum convite pendente.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                    <thead>
                                        <tr>
                                            <th>Usuario</th>
                                            <th>Filial</th>
                                            <th>E-mail</th>
                                            <th>Tipo</th>
                                            <th>Acao</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($convitesPendentes as $convite)
                                            <tr>
                                                <td>{{ optional($convite->user)->name ?? '-' }}</td>
                                                <td>{{ optional(optional($convite->user)->filial)->nome ?? '-' }}</td>
                                                <td>{{ optional($convite->user)->email ?? '-' }}</td>
                                                <td>
                                                    @if ($convite->user && $convite->user->isSupervisor())
                                                        Porteiro
                                                    @elseif ($convite->user && $convite->user->isRh())
                                                        RH
                                                    @elseif ($convite->user && $convite->user->isGestor())
                                                        Gestor
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($convite->user)
                                                        <form method="POST" action="{{ route('equipe.convites.resend', $convite) }}">
                                                            @csrf
                                                            <button type="submit" class="btn btn-outline-primary btn-sm">Reenviar</button>
                                                        </form>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function setupSelectFilter(inputId, selectId) {
            var input = document.getElementById(inputId);
            var select = document.getElementById(selectId);

            if (!input || !select) {
                return;
            }

            var options = Array.from(select.options);

            input.addEventListener('input', function () {
                var term = input.value.trim().toLowerCase();
                options.forEach(function (option, index) {
                    if (index === 0) {
                        option.hidden = false;
                        return;
                    }

                    var text = option.text.toLowerCase();
                    option.hidden = term.length > 0 && !text.includes(term);
                });
            });
        }

        setupSelectFilter('rhFilialSearch', 'rhFilialSelect');
        setupSelectFilter('supervisorFilialSearch', 'supervisorFilialSelect');
        setupSelectFilter('diaristaSearch', 'diaristaSelect');
        setupSelectFilter('diaristaFilialSearch', 'diaristaFilialSelect');
    </script>
</body>
</html>
