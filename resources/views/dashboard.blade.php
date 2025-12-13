{{-- resources/views/dashboard.blade.php --}}

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Controle de Diárias</title>

    {{-- CSS do Bootstrap --}}
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    {{-- Ícones do Bootstrap (para o ícone de câmera) --}}
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    {{-- CSS do FullCalendar --}}
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/main.min.css">

    <style>
        body {
            padding-top: 60px; /* espaço para a navbar fixa */
            background-color: #f4f4f4;
        }

        #dashboardLayout {
            max-width: 1400px;
            margin: 0 auto;
        }

        #calendar {
            width: 100%;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 0.75rem;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .fc-daygrid-day {
            cursor: pointer;
        }

        .calendar-title {
            font-size: 1.6rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .sidebar-box {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 1rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 991.98px) {
            body {
                padding-top: 56px;
            }

            .navbar .navbar-brand {
                font-size: 1rem;
            }

            .navbar-text {
                font-size: 0.8rem;
            }

            #calendar {
                padding: 0.4rem;
            }

            .calendar-title {
                font-size: 1.3rem;
                text-align: center;
            }

            .sidebar-box {
                margin-top: 1rem;
            }

            .btn {
                font-size: 0.85rem;
            }

            .fc .fc-toolbar.fc-header-toolbar {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.25rem;
            }

            .fc .fc-toolbar-chunk {
                display: flex;
                flex-wrap: wrap;
                gap: 0.25rem;
            }

            .fc .fc-toolbar-title {
                font-size: 1rem;
            }

            .fc .fc-button {
                padding: 0.25rem 0.45rem;
                font-size: 0.75rem;
            }

            .fc-daygrid-event {
                font-size: 0.7rem;
                padding: 0.05rem 0.15rem;
            }
        }

        @media (max-width: 575.98px) {
            .navbar .navbar-brand {
                font-size: 0.9rem;
            }

            .navbar-text {
                display: none;
            }
        }
    </style>
</head>
<body>
    {{-- Navbar fixa no topo --}}
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('dashboard') }}">Controle de Diárias</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarConteudo"
                    aria-controls="navbarConteudo"
                    aria-expanded="false"
                    aria-label="Alternar navegação">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarConteudo">
                <div class="d-flex ms-auto align-items-center gap-3 flex-wrap">
                    @if ($user->isFuncionario())
                        <a href="{{ route('daily_requests.my') }}"
                           class="btn btn-outline-light btn-sm">
                            Solicitações
                        </a>
                    @endif

                    {{-- Empresa, gerente ou porteiro podem abrir o leitor de QR --}}
                    @if ($user->isEmpresa() || $user->isGerente() || $user->isPorteiro())
                        <a href="{{ route('presenca.scanner') }}"
                           class="btn btn-outline-light btn-sm d-flex align-items-center gap-1">
                            <i class="bi bi-camera"></i>
                            Ler QR de Presença
                        </a>
                        <a href="{{ route('presenca.escalados') }}"
                           class="btn btn-outline-light btn-sm d-flex align-items-center gap-1">
                            <i class="bi bi-people"></i>
                            Escalados
                        </a>
                    @endif

                    @if ($user->isEmpresa() || $user->isGerente())
                        <a href="{{ route('billing.index') }}"
                           class="btn btn-outline-warning btn-sm">
                            Assinatura
                        </a>
                        <a href="{{ route('reports.index') }}"
                           class="btn btn-warning btn-sm">
                            Relatorios
                        </a>
                    @endif

                    @auth
                        <a href="{{ route('funcionarios.show', $user->id) }}"
                           class="btn btn-outline-light btn-sm">
                            Meu QR de Presença
                        </a>
                    @endauth

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
        </div>
    </nav>

    {{-- Conteúdo principal --}}
    <div class="container-fluid mt-4">
        <div class="row g-3" id="dashboardLayout">
            <div class="col-12 col-xl-10 col-lg-9">
                <h4 class="calendar-title">Calendário de Diárias</h4>
                <div id="calendar"></div>
            </div>

            <div class="col-12 col-xl-2 col-lg-3">
                <div class="sidebar-box">
                    @if ($user->isFuncionario())
                        <h4>Solicitar Diária</h4>
                        <p>
                            Clique em um dia do calendário para ver os horários cadastrados.
                            Escolha um horário com vagas disponíveis no modal e envie sua solicitação.
                        </p>
                    @endif

                    @if ($user->isEmpresa())
                        <h4>Painel da Empresa</h4>
                        <a href="{{ route('daily_requests.index') }}"
                           class="btn btn-info w-100 mb-3">
                            Ver Solicitações dos Funcionários
                        </a>
                        <p>
                            Clique em um dia do calendário para adicionar horários (turnos) com
                            quantidade de vagas. Esses horários aparecerão no calendário e no
                            modal dos funcionários.
                        </p>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success mt-3">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger mt-3">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Modal para escolher HORÁRIO (somente FUNCIONÁRIO) --}}
    @if ($user->isFuncionario())
        <div class="modal fade" id="modalHorarios" tabindex="-1"
             aria-labelledby="modalHorariosLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form id="formDiaria" method="POST" action="{{ route('daily_requests.store') }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalHorariosLabel">
                                Solicitar Diária
                            </h5>
                            <button type="button" class="btn-close"
                                    data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="data_diaria" id="inputDataDiaria">

                            <div class="mb-3">
                                <label class="form-label">Data da diária</label>
                                <input type="text" class="form-control" id="dataFormatada" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Escolha um horário</label>
                                <div id="listaHorarios">
                                    <p class="text-muted mb-0">Carregando horários...</p>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="observacoes" class="form-label">
                                    Observações (opcional)
                                </label>
                                <textarea name="observacoes" id="observacoes"
                                          rows="3" class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button"
                                    class="btn btn-secondary"
                                    data-bs-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                Enviar Solicitação
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal para ADICIONAR HORÁRIO (somente EMPRESA) --}}
    @if ($user->role === 'empresa')
        <div class="modal fade" id="modalDemanda" tabindex="-1"
             aria-labelledby="modalDemandaLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalDemandaLabel">
                            Adicionar horário (turno) para o dia
                        </h5>
                        <button type="button" class="btn-close"
                                data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>

                    <div class="modal-body">
                        <form id="formTurno">
                            <input type="hidden" id="turno_data_diaria" name="data_diaria">

                            <div class="mb-3">
                                <label for="turno_hora_inicio" class="form-label">
                                    Hora de início
                                </label>
                                <input type="time"
                                       class="form-control"
                                       id="turno_hora_inicio"
                                       name="hora_inicio"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="turno_hora_fim" class="form-label">
                                    Hora de término
                                </label>
                                <input type="time"
                                       class="form-control"
                                       id="turno_hora_fim"
                                       name="hora_fim"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="turno_vagas" class="form-label">
                                    Vagas para este horário
                                </label>
                                <input type="number"
                                       min="1"
                                       class="form-control"
                                       id="turno_vagas"
                                       name="vagas_totais"
                                       required>
                            </div>

                            <small class="text-muted d-block mb-2">
                                Exemplo: 55 vagas das 18:00 às 02:00; 30 vagas das 03:00 às 11:00;
                                60 vagas das 23:45 às 08:15. Você pode salvar um horário e em seguida
                                adicionar outro para o mesmo dia.
                            </small>

                            <button type="submit" class="btn btn-success w-100">
                                Adicionar horário
                            </button>
                        </form>
                    </div>

                    <div class="modal-footer">
                        <button type="button"
                                class="btn btn-secondary"
                                data-bs-dismiss="modal">
                            Fechar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- JS do Bootstrap --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    {{-- JS do FullCalendar --}}
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales-all.global.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var calendarEl = document.getElementById('calendar');

            var userRole       = @json($user->role);
            var salvarTurnoUrl = @json(route('daily_shifts.store'));
            var csrfToken      = @json(csrf_token());
            var rotaEvents     = @json(route('calendar.events'));
            var rotaTurnosBase = @json(url('/daily-shifts'));

            // ------------------ FUNCIONÁRIO: modal de horários ------------------
            @if ($user->isFuncionario())
            var modalHorariosEl    = document.getElementById('modalHorarios');
            var modalHorarios      = new bootstrap.Modal(modalHorariosEl);
            var listaHorariosDiv   = document.getElementById('listaHorarios');
            var inputDataDiaria    = document.getElementById('inputDataDiaria');
            var campoDataFormatada = document.getElementById('dataFormatada');

            function abrirModalParaData(dateStr) {
                inputDataDiaria.value = dateStr;

                var partes = dateStr.split('-'); // YYYY-MM-DD
                if (partes.length === 3) {
                    campoDataFormatada.value = partes[2] + '/' + partes[1] + '/' + partes[0];
                } else {
                    campoDataFormatada.value = dateStr;
                }

                listaHorariosDiv.innerHTML =
                    '<p class="text-muted mb-0">Carregando horários...</p>';

                fetch(rotaTurnosBase + '/' + encodeURIComponent(dateStr))
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Erro ao buscar horários');
                        }
                        return response.json();
                    })
                    .then(function (data) {
                        if (!Array.isArray(data) || data.length === 0) {
                            listaHorariosDiv.innerHTML =
                                '<p class="text-danger mb-0">Não há horários cadastrados para este dia.</p>';
                            return;
                        }

                        var html = '';

                        data.forEach(function (turno) {
                            var label = turno.hora_inicio.substring(0, 5) + ' às ' +
                                        turno.hora_fim.substring(0, 5) +
                                        ' · vagas: ' + turno.vagas_restantes +
                                        '/' + turno.vagas_totais;

                            var disabled = turno.vagas_restantes <= 0 ? 'disabled' : '';

                            html += '<div class="form-check mb-1">' +
                                        '<input class="form-check-input" type="radio" ' +
                                            'name="daily_shift_id" ' +
                                            'id="shift_' + turno.id + '" ' +
                                            'value="' + turno.id + '" ' + disabled + '>' +
                                        '<label class="form-check-label" for="shift_' + turno.id + '">' +
                                            label +
                                        '</label>' +
                                    '</div>';
                        });

                        listaHorariosDiv.innerHTML = html;
                    })
                    .catch(function (error) {
                        console.error(error);
                        listaHorariosDiv.innerHTML =
                            '<p class="text-danger mb-0">Erro ao carregar horários. Tente novamente.</p>';
                    });

                modalHorarios.show();
            }
            @endif
            // -------------------------------------------------------------------

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'pt-br',
                events: rotaEvents,

                dateClick: function (info) {
                    if (userRole === 'empresa') {
                        var campoDataTurno = document.getElementById('turno_data_diaria');
                        if (campoDataTurno) {
                            campoDataTurno.value = info.dateStr;
                        }

                        var modalEl = document.getElementById('modalDemanda');
                        if (modalEl) {
                            var modal = new bootstrap.Modal(modalEl);
                            modal.show();
                        }
                        return;
                    }

                    if (userRole === 'funcionario') {
                        @if ($user->isFuncionario())
                            abrirModalParaData(info.dateStr);
                        @endif
                    }
                },

                eventClick: function (info) {
                    var dateStr = info.event.extendedProps.data_diaria
                                  || info.event.startStr.substring(0, 10);

                    if (userRole === 'empresa') {
                        var campoDataTurno = document.getElementById('turno_data_diaria');
                        if (campoDataTurno) {
                            campoDataTurno.value = dateStr;
                        }

                        var modalEl = document.getElementById('modalDemanda');
                        if (modalEl) {
                            var modal = new bootstrap.Modal(modalEl);
                            modal.show();
                        }
                    }

                    if (userRole === 'funcionario') {
                        @if ($user->isFuncionario())
                            abrirModalParaData(dateStr);
                        @endif
                    }
                }
            });

            calendar.render();

            // ------------------ EMPRESA: envio do HORÁRIO via AJAX ------------------
            var formTurno = document.getElementById('formTurno');
            if (formTurno) {
                formTurno.addEventListener('submit', function (event) {
                    event.preventDefault();

                    var dataDiaria = document.getElementById('turno_data_diaria').value;
                    var horaInicio = document.getElementById('turno_hora_inicio').value;
                    var horaFim    = document.getElementById('turno_hora_fim').value;
                    var vagas      = document.getElementById('turno_vagas').value;

                    fetch(salvarTurnoUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            data_diaria:  dataDiaria,
                            hora_inicio:  horaInicio,
                            hora_fim:     horaFim,
                            vagas_totais: vagas
                        })
                    })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Erro ao salvar horário');
                        }
                        return response.json();
                    })
                    .then(function () {
                        document.getElementById('turno_hora_inicio').value = '';
                        document.getElementById('turno_hora_fim').value    = '';
                        document.getElementById('turno_vagas').value       = '';

                        calendar.refetchEvents();

                        alert('Horário adicionado com sucesso. Você pode adicionar outro horário para o mesmo dia.');
                    })
                    .catch(function (error) {
                        console.error(error);
                        alert('Não foi possível salvar o horário. Atualize a página e tente novamente.');
                    });
                });
            }
        });
    </script>
</body>
</html>


