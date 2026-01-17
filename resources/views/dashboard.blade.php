<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Controle de Diarias</title>

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/main.min.css">

    <style>
        body {
            padding-top: 60px;
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
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('dashboard') }}">Controle de Diarias</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarConteudo"
                    aria-controls="navbarConteudo"
                    aria-expanded="false"
                    aria-label="Alternar navegacao">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarConteudo">
                <div class="d-flex ms-auto align-items-center gap-3 flex-wrap">
                    @if ($user->isDiarista())
                        <a href="{{ route('daily_requests.my') }}"
                           class="btn btn-outline-light btn-sm">
                            Solicitacoes
                        </a>
                        <a href="{{ route('pagamentos.index') }}"
                           class="btn btn-outline-light btn-sm">
                            Pagamentos
                        </a>
                        <a href="{{ route('diaristas.profile.edit') }}"
                           class="btn btn-outline-light btn-sm">
                            Meus dados
                        </a>
                    @endif

                    @if ($user->isSupervisor())
                        <a href="{{ route('presenca.escalados', ['data' => \Carbon\Carbon::today()->toDateString()]) }}"
                           class="btn btn-outline-light btn-sm d-flex align-items-center gap-1">
                            <i class="bi bi-calendar-day"></i>
                            Presenca
                        </a>
                    @endif

                    @if ($user->isGestor() || $user->isRh())
                        <a href="{{ route('daily_requests.index') }}"
                           class="btn btn-outline-light btn-sm">
                            Solicitacoes
                        </a>
                    @endif

                    @if ($user->isGestor())
                        <a href="{{ route('reports.index') }}"
                           class="btn btn-outline-light btn-sm">
                            Relatorios
                        </a>
                        <a href="{{ route('equipe.index') }}"
                           class="btn btn-outline-light btn-sm">
                            Equipe
                        </a>
                        <a href="{{ route('billing.index') }}"
                           class="btn btn-outline-light btn-sm">
                            Assinatura
                        </a>
                    @endif

                    <span class="navbar-text text-white">
                        Logado como: {{ $user->name }} ({{ $user->role }})
                    </span>

                    <form method="POST" action="{{ route('auth.logout', [], false) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-light btn-sm">
                            Sair
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row g-3" id="dashboardLayout">
            <div class="col-12">
                <h4 class="calendar-title">Calendario de Diarias</h4>
                <div id="calendar"></div>

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

    @if ($user->isDiarista())
        <div class="modal fade" id="modalHorarios" tabindex="-1"
             aria-labelledby="modalHorariosLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form id="formDiaria" method="POST" action="{{ route('daily_requests.store', [], false) }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalHorariosLabel">
                                Solicitar Diária
                            </h5>
                            <button type="button" class="btn-close"
                                    data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="data_diaria" id="inputDataDiaria" value="{{ old('data_diaria') }}">

                            <div class="mb-3">
                                <label class="form-label">Data da diária</label>
                                <input type="text" class="form-control" id="dataFormatada" readonly
                                       value="{{ old('data_diaria') ? \Carbon\Carbon::parse(old('data_diaria'))->format('d/m/Y') : '' }}">
                            </div>

                            @if ($errors->diaria->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->diaria->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

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

    @if ($user->podeGerenciarEscala())
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

                            @if (! $user->filial_id)
                                <div class="mb-3">
                                    <label for="turno_filial_id" class="form-label">Filial</label>
                                    <select class="form-select" id="turno_filial_id" name="filial_id" required>
                                        <option value="">Selecione uma filial</option>
                                        @forelse ($filiais ?? [] as $filial)
                                            <option value="{{ $filial->id }}">
                                                {{ $filial->nome }}@if ($filial->cidade) - {{ $filial->cidade }}@endif
                                            </option>
                                        @empty
                                            <option value="">Nenhuma filial cadastrada</option>
                                        @endforelse
                                    </select>
                                </div>
                            @endif

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

    @if ($user->podeGerenciarEscala())
        <div class="modal fade" id="modalEditarTurno" tabindex="-1"
             aria-labelledby="modalEditarTurnoLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditarTurnoLabel">
                            Editar horario do dia
                        </h5>
                        <button type="button" class="btn-close"
                                data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formEditarTurno">
                            <input type="hidden" id="editar_turno_id">

                            <div class="mb-3">
                                <label class="form-label">Data</label>
                                <input type="text" class="form-control" id="editar_turno_data" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Hora de inicio</label>
                                <input type="time"
                                       class="form-control"
                                       id="editar_turno_hora_inicio"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Hora de termino</label>
                                <input type="time"
                                       class="form-control"
                                       id="editar_turno_hora_fim"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Vagas</label>
                                <input type="number"
                                       min="1"
                                       class="form-control"
                                       id="editar_turno_vagas"
                                       required>
                            </div>

                            <div class="small text-muted" id="editar_turno_criado_por"></div>

                            <button type="submit" class="btn btn-primary w-100 mt-3">
                                Salvar alteracoes
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales-all.global.min.js"></script>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            var calendarEl = document.getElementById('calendar');

            var userIsDiarista = @json($user->isDiarista());
            var canManageCalendar = @json($user->podeGerenciarEscala());
            var salvarTurnoUrl = @json(route('daily_shifts.store', [], false));
            var csrfToken      = @json(csrf_token());
            var rotaEvents     = @json('/calendar/events');
            var rotaTurnosBase = @json('/daily-shifts');

            // ------------------ DIARISTA: modal de horarios ------------------
            @if ($user->isDiarista())
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
                    '<p class="text-muted mb-0">Carregando horarios...</p>';

                fetch(rotaTurnosBase + '/' + encodeURIComponent(dateStr), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Erro ao buscar horarios');
                        }
                        return response.json();
                    })
                    .then(function (data) {
                        if (!Array.isArray(data) || data.length === 0) {
                            listaHorariosDiv.innerHTML =
                                '<p class="text-danger mb-0">Nao ha horarios cadastrados para este dia.</p>';
                            return;
                        }

                        var html = '';

                        data.forEach(function (turno) {
                            var label = turno.hora_inicio.substring(0, 5) + ' as ' +
                                        turno.hora_fim.substring(0, 5) +
                                        ' - vagas: ' + turno.vagas_restantes +
                                        '/' + turno.vagas_totais;

                            var checked = dailyShiftOld && String(turno.id) === String(dailyShiftOld)
                                ? 'checked'
                                : '';
                            var disabled = turno.vagas_restantes <= 0 ? 'disabled' : '';

                            html += '<div class="form-check mb-1">' +
                                        '<input class="form-check-input" type="radio" ' +
                                            'name="daily_shift_id" ' +
                                            'id="shift_' + turno.id + '" ' +
                                            'value="' + turno.id + '" ' + checked + ' ' + disabled + '>' +
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
                            '<p class="text-danger mb-0">Erro ao carregar horarios. Tente novamente.</p>';
                    });

                modalHorarios.show();
            }

            var diariaErro = @json($errors->diaria->any());
            var dataDiariaOld = @json(old('data_diaria'));
            var dailyShiftOld = @json(old('daily_shift_id'));

            if (diariaErro && dataDiariaOld) {
                abrirModalParaData(dataDiariaOld);
            }
            @endif
            // -------------------------------------------------------------------

            var modalEditarTurnoEl = document.getElementById('modalEditarTurno');
            var modalEditarTurno = modalEditarTurnoEl ? new bootstrap.Modal(modalEditarTurnoEl) : null;
            var editarTurnoId = document.getElementById('editar_turno_id');
            var editarTurnoData = document.getElementById('editar_turno_data');
            var editarHoraInicio = document.getElementById('editar_turno_hora_inicio');
            var editarHoraFim = document.getElementById('editar_turno_hora_fim');
            var editarVagas = document.getElementById('editar_turno_vagas');
            var editarCriadoPor = document.getElementById('editar_turno_criado_por');

            function formatDateLabel(dateStr) {
                if (!dateStr) {
                    return '';
                }

                var parts = dateStr.split('-');
                if (parts.length === 3) {
                    return parts[2] + '/' + parts[1] + '/' + parts[0];
                }

                return dateStr;
            }

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'pt-br',
                events: function (info, successCallback, failureCallback) {
                    var url = rotaEvents + '?start=' + encodeURIComponent(info.startStr)
                        + '&end=' + encodeURIComponent(info.endStr);

                    fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Erro ao carregar eventos.');
                        }
                        return response.json();
                    })
                    .then(successCallback)
                    .catch(function (error) {
                        console.error(error);
                        failureCallback(error);
                    });
                },

                dateClick: function (info) {
                    if (info.jsEvent) {
                        info.jsEvent.preventDefault();
                    }

                    if (canManageCalendar) {
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

                    if (userIsDiarista) {
                        @if ($user->isDiarista())
                            abrirModalParaData(info.dateStr);
                        @endif
                    }
                },

                eventClick: function (info) {
                    if (info.jsEvent) {
                        info.jsEvent.preventDefault();
                    }

                    var dateStr = info.event.extendedProps.data_diaria
                                  || info.event.startStr.substring(0, 10);

                    if (canManageCalendar && modalEditarTurno) {
                        if (editarTurnoId) {
                            editarTurnoId.value = info.event.extendedProps.shift_id || '';
                        }

                        if (editarTurnoData) {
                            editarTurnoData.value = formatDateLabel(dateStr);
                        }

                        if (editarHoraInicio) {
                            editarHoraInicio.value = (info.event.extendedProps.hora_inicio || '').substring(0, 5);
                        }

                        if (editarHoraFim) {
                            editarHoraFim.value = (info.event.extendedProps.hora_fim || '').substring(0, 5);
                        }

                        if (editarVagas) {
                            editarVagas.value = info.event.extendedProps.vagas_totais || '';
                        }

                        if (editarCriadoPor) {
                            var criadoPor = info.event.extendedProps.created_by_name;
                            editarCriadoPor.textContent = criadoPor
                                ? 'Criado por: ' + criadoPor
                                : 'Criado por: -';
                        }

                        modalEditarTurno.show();
                        return;
                    }

                    if (userIsDiarista) {
                        @if ($user->isDiarista())
                            abrirModalParaData(dateStr);
                        @endif
                    }
                }
            });

            calendar.render();

            // ------------------ EMPRESA: envio do HORARIO via AJAX ------------------
            var formTurno = document.getElementById('formTurno');
            if (formTurno) {
                formTurno.addEventListener('submit', function (event) {
                    event.preventDefault();

                    var dataDiaria = document.getElementById('turno_data_diaria').value;
                    var filialSelect = document.getElementById('turno_filial_id');
                    var filialId = filialSelect ? filialSelect.value : null;
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
                            vagas_totais: vagas,
                            filial_id: filialId
                        })
                    })
                    .then(function (response) {
                        return response.text().then(function (text) {
                            var payload = null;
                            var contentType = response.headers.get('content-type') || '';

                            if (text) {
                                try {
                                    payload = JSON.parse(text);
                                } catch (e) {
                                    payload = null;
                                }
                            }

                            if (!response.ok) {
                                var message = 'Nao foi possivel salvar o horario.';
                                if (response.status === 419) {
                                    message = 'Sessao expirada. Recarregue a pagina e tente novamente.';
                                } else if (response.status === 403) {
                                    message = 'Voce nao tem permissao para criar horarios.';
                                } else if (response.status === 402) {
                                    message = 'Assinatura pendente. Regularize para continuar.';
                                }

                                if (payload && payload.message) {
                                    message = payload.message;
                                } else if (payload && payload.errors) {
                                    var errorMessages = [];
                                    Object.keys(payload.errors).forEach(function (key) {
                                        errorMessages = errorMessages.concat(payload.errors[key]);
                                    });
                                    if (errorMessages.length) {
                                        message = errorMessages.join(' ');
                                    }
                                } else if (!payload && contentType.indexOf('text/html') !== -1) {
                                    message = 'Sessao expirada ou acesso bloqueado. Recarregue a pagina.';
                                }

                                throw new Error(message);
                            }

                            if (!payload) {
                                throw new Error('Resposta inesperada do servidor. Recarregue a pagina.');
                            }

                            return payload;
                        });
                    })
                    .then(function () {
                        document.getElementById('turno_hora_inicio').value = '';
                        document.getElementById('turno_hora_fim').value    = '';
                        document.getElementById('turno_vagas').value       = '';

                        calendar.refetchEvents();

                        alert('Horario adicionado com sucesso. Voce pode adicionar outro horario para o mesmo dia.');
                    })
                    .catch(function (error) {
                        console.error(error);
                        alert(error && error.message ? error.message : 'Nao foi possivel salvar o horario.');
                    });
                });
            }

            var formEditarTurno = document.getElementById('formEditarTurno');
            if (formEditarTurno) {
                formEditarTurno.addEventListener('submit', function (event) {
                    event.preventDefault();

                    if (!editarTurnoId || !editarTurnoId.value) {
                        alert('Selecione um horario para editar.');
                        return;
                    }

                    var shiftId = editarTurnoId.value;
                    var horaInicio = editarHoraInicio ? editarHoraInicio.value : '';
                    var horaFim = editarHoraFim ? editarHoraFim.value : '';
                    var vagas = editarVagas ? editarVagas.value : '';

                    fetch(rotaTurnosBase + '/' + encodeURIComponent(shiftId), {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            hora_inicio: horaInicio,
                            hora_fim: horaFim,
                            vagas_totais: vagas
                        })
                    })
                    .then(function (response) {
                        return response.text().then(function (text) {
                            var payload = null;
                            var contentType = response.headers.get('content-type') || '';

                            if (text) {
                                try {
                                    payload = JSON.parse(text);
                                } catch (e) {
                                    payload = null;
                                }
                            }

                            if (!response.ok) {
                                var message = 'Nao foi possivel atualizar o horario.';
                                if (response.status === 419) {
                                    message = 'Sessao expirada. Recarregue a pagina e tente novamente.';
                                } else if (response.status === 403) {
                                    message = 'Voce nao tem permissao para atualizar horarios.';
                                } else if (response.status === 402) {
                                    message = 'Assinatura pendente. Regularize para continuar.';
                                }

                                if (payload && payload.message) {
                                    message = payload.message;
                                } else if (payload && payload.errors) {
                                    var errorMessages = [];
                                    Object.keys(payload.errors).forEach(function (key) {
                                        errorMessages = errorMessages.concat(payload.errors[key]);
                                    });
                                    if (errorMessages.length) {
                                        message = errorMessages.join(' ');
                                    }
                                } else if (!payload && contentType.indexOf('text/html') !== -1) {
                                    message = 'Sessao expirada ou acesso bloqueado. Recarregue a pagina.';
                                }

                                throw new Error(message);
                            }

                            if (!payload) {
                                throw new Error('Resposta inesperada do servidor. Recarregue a pagina.');
                            }

                            return payload;
                        });
                    })
                    .then(function () {
                        calendar.refetchEvents();
                        if (modalEditarTurno) {
                            modalEditarTurno.hide();
                        }
                        alert('Horario atualizado com sucesso.');
                    })
                    .catch(function (error) {
                        console.error(error);
                        alert(error && error.message ? error.message : 'Nao foi possivel atualizar o horario.');
                    });
                });
            }
        });
    </script>
</body>
</html>




