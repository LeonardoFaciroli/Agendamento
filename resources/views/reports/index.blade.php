<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
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
            <h5 class="card-title">Pagamentos pendentes</h5>
            <p class="text-muted mb-3">Liste funcionarios com presenca completa (entrada e saida) e pagamentos pendentes.</p>
            <div class="row g-2">
                <div class="col-12 col-md-3">
                    <label class="form-label">Data inicial</label>
                    <input type="date" id="pagamentosDataInicial" value="{{ $inicio }}" class="form-control">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Data final</label>
                    <input type="date" id="pagamentosDataFinal" value="{{ $fim }}" class="form-control">
                </div>
                <div class="col-12 col-md-3 d-flex align-items-end">
                    <button type="button" class="btn btn-primary w-100" id="abrirPagamentosPendentes" data-bs-toggle="modal" data-bs-target="#modalPagamentosPendentes">
                        Ver pagamentos pendentes
                    </button>
                </div>
            </div>
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


<div class="modal fade" id="modalPagamentosPendentes" tabindex="-1" aria-labelledby="modalPagamentosPendentesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPagamentosPendentesLabel">Pagamentos pendentes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <small class="text-muted" id="pagamentosFiltroLabel"></small>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="atualizarPagamentosPendentes">Atualizar lista</button>
                </div>
                <div id="pagamentosPendentesEmpty" class="text-muted">Carregando...</div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>CPF</th>
                                <th>PIX</th>
                                <th>Dias pendentes</th>
                                <th>Acao</th>
                            </tr>
                        </thead>
                        <tbody id="pagamentosPendentesBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalConfirmarPagamento" tabindex="-1" aria-labelledby="modalConfirmarPagamentoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('pagamentos.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="user_id" id="pagamentoUserId">
                <input type="hidden" name="data_inicial" id="pagamentoDataInicial">
                <input type="hidden" name="data_final" id="pagamentoDataFinal">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalConfirmarPagamentoLabel">Registrar pagamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <div><strong>Nome:</strong> <span id="pagamentoNome"></span></div>
                        <div><strong>CPF:</strong> <span id="pagamentoCpf"></span></div>
                        <div><strong>PIX:</strong> <span id="pagamentoPix"></span></div>
                        <div><strong>Dias pendentes:</strong> <span id="pagamentoDiasPendentes"></span></div>
                    </div>

                    <div class="mb-3">
                        <label for="pagamentoDiasPagar" class="form-label">Dias a pagar</label>
                        <input type="number" class="form-control" id="pagamentoDiasPagar" name="dias_pagos" min="1" required>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="pagamentoComprovanteToggle">
                        <label class="form-check-label" for="pagamentoComprovanteToggle">Incluir comprovante</label>
                    </div>

                    <div class="mb-3" id="pagamentoComprovanteGroup" style="display: none;">
                        <label for="pagamentoComprovante" class="form-label">Imagem do comprovante</label>
                        <input class="form-control" type="file" id="pagamentoComprovante" name="comprovante" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Marcar como pago</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var pendentesUrl = @json('/pagamentos/pendentes');
        var btnAbrir = document.getElementById('abrirPagamentosPendentes');
        var btnAtualizar = document.getElementById('atualizarPagamentosPendentes');
        var body = document.getElementById('pagamentosPendentesBody');
        var empty = document.getElementById('pagamentosPendentesEmpty');
        var filtroLabel = document.getElementById('pagamentosFiltroLabel');

        function escapeHtml(value) {
            return String(value || '').replace(/[&<>"']/g, function (ch) {
                var map = {"&": "&amp;", "<": "&lt;", ">": "&gt;", "\"": "&quot;", "'": "&#39;"};
                return map[ch] || ch;
            });
        }

        function carregarPendentes() {
            if (!body || !empty) {
                return;
            }

            var dataInicial = document.getElementById('pagamentosDataInicial').value;
            var dataFinal = document.getElementById('pagamentosDataFinal').value;

            var query = [];
            if (dataInicial) {
                query.push('data_inicial=' + encodeURIComponent(dataInicial));
            }
            if (dataFinal) {
                query.push('data_final=' + encodeURIComponent(dataFinal));
            }

            var url = pendentesUrl + (query.length ? '?' + query.join('&') : '');

            empty.textContent = 'Carregando...';
            body.innerHTML = '';

            var filtroTexto = 'Periodo: ' + (dataInicial || 'inicio') + ' - ' + (dataFinal || 'hoje');
            if (filtroLabel) {
                filtroLabel.textContent = filtroTexto;
            }

            fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Erro ao carregar pagamentos pendentes.');
                }
                return response.json();
            })
            .then(function (payload) {
                var data = payload && payload.data ? payload.data : [];
                if (!data.length) {
                    empty.textContent = 'Nenhum pagamento pendente para o periodo informado.';
                    return;
                }

                empty.textContent = '';

                data.forEach(function (item) {
                    var row = document.createElement('tr');

                    row.innerHTML = ''
                        + '<td>' + escapeHtml(item.nome || '-') + '</td>'
                        + '<td>' + escapeHtml(item.cpf || '-') + '</td>'
                        + '<td>' + escapeHtml(item.pix || '-') + '</td>'
                        + '<td>' + (item.dias_pendentes || 0) + '</td>'
                        + '<td>'
                        + '  <button type="button" class="btn btn-sm btn-success" '
                        + '    data-bs-toggle="modal" data-bs-target="#modalConfirmarPagamento" data-bs-dismiss="modal" '
                        + '    data-user-id="' + item.user_id + '" '
                        + '    data-nome="' + escapeHtml(item.nome || '') + '" '
                        + '    data-cpf="' + escapeHtml(item.cpf || '') + '" '
                        + '    data-pix="' + escapeHtml(item.pix || '') + '" '
                        + '    data-dias="' + (item.dias_pendentes || 0) + '" '
                        + '  >Pagamento</button>'
                        + '</td>';

                    body.appendChild(row);
                });
            })
            .catch(function (error) {
                console.error(error);
                empty.textContent = 'Erro ao carregar pagamentos pendentes.';
            });
        }

        if (btnAbrir) {
            btnAbrir.addEventListener('click', carregarPendentes);
        }

        if (btnAtualizar) {
            btnAtualizar.addEventListener('click', carregarPendentes);
        }

        var modalConfirmar = document.getElementById('modalConfirmarPagamento');
        if (modalConfirmar) {
            modalConfirmar.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                if (!button) {
                    return;
                }

                var nome = button.getAttribute('data-nome') || '';
                var cpf = button.getAttribute('data-cpf') || '';
                var pix = button.getAttribute('data-pix') || '';
                var dias = button.getAttribute('data-dias') || '0';
                var userId = button.getAttribute('data-user-id');

                var dataInicial = document.getElementById('pagamentosDataInicial').value;
                var dataFinal = document.getElementById('pagamentosDataFinal').value;

                document.getElementById('pagamentoUserId').value = userId;
                document.getElementById('pagamentoDataInicial').value = dataInicial || '';
                document.getElementById('pagamentoDataFinal').value = dataFinal || '';

                document.getElementById('pagamentoNome').textContent = nome || '-';
                document.getElementById('pagamentoCpf').textContent = cpf || '-';
                document.getElementById('pagamentoPix').textContent = pix || '-';
                document.getElementById('pagamentoDiasPendentes').textContent = dias;

                var diasInput = document.getElementById('pagamentoDiasPagar');
                diasInput.value = dias;
                diasInput.max = dias;

                var comprovanteToggle = document.getElementById('pagamentoComprovanteToggle');
                var comprovanteGroup = document.getElementById('pagamentoComprovanteGroup');
                var comprovanteInput = document.getElementById('pagamentoComprovante');

                comprovanteToggle.checked = false;
                comprovanteGroup.style.display = 'none';
                comprovanteInput.value = '';
            });
        }

        var comprovanteToggle = document.getElementById('pagamentoComprovanteToggle');
        var comprovanteGroup = document.getElementById('pagamentoComprovanteGroup');
        if (comprovanteToggle && comprovanteGroup) {
            comprovanteToggle.addEventListener('change', function () {
                comprovanteGroup.style.display = comprovanteToggle.checked ? 'block' : 'none';
            });
        }
    });
</script>

</body>
</html>
