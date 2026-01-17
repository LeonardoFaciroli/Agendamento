<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Empresas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Cadastro de empresas</h3>
            <form method="POST" action="{{ route('auth.logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-primary btn-sm">Sair</button>
            </form>
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

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Novo convite</h5>
                <form method="POST" action="{{ route('admin.empresas.store') }}" class="row g-3">
                    @csrf
                    <div class="col-md-6">
                        <label class="form-label">Nome da empresa</label>
                        <input type="text" name="empresa_nome" class="form-control" value="{{ old('empresa_nome') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Filial</label>
                        <input type="text" name="filial_nome" class="form-control" value="{{ old('filial_nome') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cidade (opcional)</label>
                        <input type="text" name="filial_cidade" class="form-control" value="{{ old('filial_cidade') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nome do gestor</label>
                        <input type="text" name="admin_name" class="form-control" value="{{ old('admin_name') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">E-mail do gestor</label>
                        <input type="email" name="admin_email" class="form-control" value="{{ old('admin_email') }}" required>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary w-100">Enviar convite</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Novo porteiro</h5>
                        <form method="POST" action="{{ route('admin.porteiros.store') }}" class="row g-3">
                            @csrf
                            <div class="col-12">
                                <label class="form-label">Nome do porteiro</label>
                                <input type="text" name="supervisor_nome" class="form-control" value="{{ old('supervisor_nome') }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">E-mail do porteiro</label>
                                <input type="email" name="supervisor_email" class="form-control" value="{{ old('supervisor_email') }}" required>
                            </div>
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
                                            {{ $filial->empresa->nome }} - {{ $filial->nome }}@if ($filial->cidade) ({{ $filial->cidade }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
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
                        <h5 class="card-title">Alterar filial do diarista</h5>
                        <form method="POST" action="{{ route('admin.diaristas.filial') }}" class="row g-3">
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
                                            {{ $filial->empresa->nome }} - {{ $filial->nome }}@if ($filial->cidade) ({{ $filial->cidade }}) @endif
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
        </div>

        <div class="row g-3">
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
                                            <th>Empresa</th>
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
                                                <td>{{ optional(optional($convite->user)->empresa)->nome ?? '-' }}</td>
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
                                                        <form method="POST" action="{{ route('admin.empresas.convites.resend', $convite) }}">
                                                            @csrf
                                                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                                                Reenviar
                                                            </button>
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

            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Empresas cadastradas</h5>
                        <div class="mb-3">
                            <input type="search" id="empresaSearch" class="form-control" placeholder="Pesquisar empresa...">
                        </div>
                        @if ($empresas->isEmpty())
                            <p class="text-muted mb-0">Nenhuma empresa cadastrada.</p>
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach ($empresas as $empresa)
                                    <a href="{{ route('admin.empresas.show', $empresa) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <span>{{ $empresa->nome }}</span>
                                        <span class="badge bg-secondary">{{ $empresa->filiais->count() }} filial(is)</span>
                                    </a>
                                @endforeach
                            </ul>
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

        function setupListFilter(inputId, listSelector) {
            var input = document.getElementById(inputId);
            var list = document.querySelector(listSelector);

            if (!input || !list) {
                return;
            }

            var items = Array.from(list.querySelectorAll('.list-group-item'));

            input.addEventListener('input', function () {
                var term = input.value.trim().toLowerCase();
                items.forEach(function (item) {
                    var text = item.textContent.toLowerCase();
                    item.classList.toggle('d-none', term.length > 0 && !text.includes(term));
                });
            });
        }

        setupSelectFilter('supervisorFilialSearch', 'supervisorFilialSelect');
        setupSelectFilter('diaristaSearch', 'diaristaSelect');
        setupSelectFilter('diaristaFilialSearch', 'diaristaFilialSelect');
        setupListFilter('empresaSearch', '.list-group');
    </script>
</body>
</html>
