<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Diarista</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h4>Cadastro de Diarista</h4>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('diaristas.store') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="name" class="form-label">Nome completo</label>
                                <input type="text"
                                       id="name"
                                       name="name"
                                       class="form-control"
                                       value="{{ old('name') }}"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail</label>
                                <input type="email"
                                       id="email"
                                       name="email"
                                       class="form-control"
                                       value="{{ old('email') }}"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="cpf" class="form-label">CPF</label>
                                <input type="text"
                                       id="cpf"
                                       name="cpf"
                                       class="form-control"
                                       value="{{ old('cpf') }}"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="telefone" class="form-label">Telefone para contato</label>
                                <input type="text"
                                       id="telefone"
                                       name="telefone"
                                       class="form-control"
                                       value="{{ old('telefone') }}"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="pix" class="form-label">Chave PIX</label>
                                <input type="text"
                                       id="pix"
                                       name="pix"
                                       class="form-control"
                                       value="{{ old('pix') }}"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="endereco" class="form-label">Endereco</label>
                                <input type="text"
                                       id="endereco"
                                       name="endereco"
                                       class="form-control"
                                       value="{{ old('endereco') }}"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="cidade" class="form-label">Cidade</label>
                                <input type="text"
                                       id="cidade"
                                       name="cidade"
                                       class="form-control"
                                       value="{{ old('cidade') }}">
                            </div>

                            <div class="mb-3">
                                <label for="funcao" class="form-label">Funcao</label>
                                <input type="text"
                                       id="funcao"
                                       name="funcao"
                                       class="form-control"
                                       value="{{ old('funcao') }}">
                            </div>

                            <div class="mb-3">
                                <label for="filial_id" class="form-label">Filial</label>
                                <select id="filial_id" name="filial_id" class="form-select" required>
                                    <option value="">Selecione uma filial</option>
                                    @foreach ($filiais as $filial)
                                        <option value="{{ $filial->id }}" {{ old('filial_id') == $filial->id ? 'selected' : '' }}>
                                            {{ $filial->empresa->nome }} - {{ $filial->nome }}@if ($filial->cidade) ({{ $filial->cidade }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Senha</label>
                                <input type="password"
                                       id="password"
                                       name="password"
                                       class="form-control"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirmar senha</label>
                                <input type="password"
                                       id="password_confirmation"
                                       name="password_confirmation"
                                       class="form-control"
                                       required>
                            </div>

                            <button type="submit" class="btn btn-success w-100">
                                Finalizar cadastro
                            </button>
                        </form>

                        <div class="mt-3 text-center">
                            <a href="{{ route('login') }}">Voltar para o login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
