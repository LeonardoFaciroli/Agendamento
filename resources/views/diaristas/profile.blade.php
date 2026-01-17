<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus dados</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('dashboard') }}">Controle de Diarias</a>
            <div class="d-flex ms-auto">
                <span class="navbar-text text-white me-3">
                    Logado como: {{ $user->name }}
                </span>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-light btn-sm">Voltar</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Meus dados</h3>
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

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('diaristas.profile.update') }}" class="row g-3">
                    @csrf

                    <div class="col-md-6">
                        <label for="name" class="form-label">Nome completo</label>
                        <input type="text"
                               id="name"
                               name="name"
                               class="form-control"
                               value="{{ old('name', $perfil->nome ?? $user->name) }}"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email"
                               id="email"
                               name="email"
                               class="form-control"
                               value="{{ old('email', $user->email) }}"
                               required>
                    </div>

                    <div class="col-md-4">
                        <label for="cpf" class="form-label">CPF</label>
                        <input type="text"
                               id="cpf"
                               name="cpf"
                               class="form-control"
                               value="{{ old('cpf', $perfil->cpf ?? '') }}"
                               required>
                    </div>

                    <div class="col-md-4">
                        <label for="telefone" class="form-label">Telefone</label>
                        <input type="text"
                               id="telefone"
                               name="telefone"
                               class="form-control"
                               value="{{ old('telefone', $perfil->telefone ?? '') }}"
                               required>
                    </div>

                    <div class="col-md-4">
                        <label for="pix" class="form-label">Chave PIX</label>
                        <input type="text"
                               id="pix"
                               name="pix"
                               class="form-control"
                               value="{{ old('pix', $perfil->pix ?? '') }}"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label for="endereco" class="form-label">Endereco</label>
                        <input type="text"
                               id="endereco"
                               name="endereco"
                               class="form-control"
                               value="{{ old('endereco', $perfil->endereco ?? '') }}"
                               required>
                    </div>

                    <div class="col-md-3">
                        <label for="cidade" class="form-label">Cidade</label>
                        <input type="text"
                               id="cidade"
                               name="cidade"
                               class="form-control"
                               value="{{ old('cidade', $perfil->cidade ?? '') }}">
                    </div>

                    <div class="col-md-3">
                        <label for="funcao" class="form-label">Funcao</label>
                        <input type="text"
                               id="funcao"
                               name="funcao"
                               class="form-control"
                               value="{{ old('funcao', $perfil->funcao ?? '') }}">
                    </div>

                    <div class="col-12">
                        <div class="alert alert-secondary mb-0">
                            Filial atual: {{ $user->filial->nome ?? 'Nao definida' }}
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary w-100">
                            Salvar alteracoes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
