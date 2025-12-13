{{-- Define que o documento é HTML5 --}}
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    {{-- Define o conjunto de caracteres --}}
    <meta charset="UTF-8">
    {{-- Define a largura de exibição para dispositivos mobile --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Título da aba do navegador --}}
    <title>Sistema de Diárias - Login e Cadastro</title>
    {{-- Importa o CSS padrão do Bootstrap de CDN para estilização rápida --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    {{-- Container principal com margem superior --}}
    <div class="container mt-5">
        {{-- Linha centralizada --}}
        <div class="row justify-content-center">
            {{-- Coluna com largura média de 8 colunas --}}
            <div class="col-md-8">
                {{-- Card que agrupa login e cadastro --}}
                <div class="card">
                    {{-- Cabeçalho do card --}}
                    <div class="card-header text-center">
                        <h4>Controle de Diárias - Login / Cadastro</h4>
                    </div>

                    {{-- Corpo do card --}}
                    <div class="card-body">
                        {{-- Exibe mensagens de erro de validação, se existirem --}}
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li> {{-- Mostra cada mensagem de erro --}}
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Exibe mensagem de sucesso, se existir na sessão --}}
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }} {{-- Mostra a mensagem de sucesso --}}
                            </div>
                        @endif

                        {{-- Cria uma linha com duas colunas: login e cadastro --}}
                        <div class="row">
                            {{-- Coluna de Login --}}
                            <div class="col-md-6 border-end">
                                <h5>Login</h5>
                                {{-- Formulário de login --}}
                                <form method="POST" action="{{ route('auth.login') }}">
                                    {{-- Token CSRF obrigatório no Laravel para formulários POST --}}
                                    @csrf

                                    {{-- Campo de e-mail --}}
                                    <div class="mb-3">
                                        <label for="login_email" class="form-label">E-mail</label>
                                        <input type="email"
                                               id="login_email"
                                               name="email"
                                               class="form-control"
                                               value="{{ old('email') }}"
                                               required>
                                    </div>

                                    {{-- Campo de senha --}}
                                    <div class="mb-3">
                                        <label for="login_password" class="form-label">Senha</label>
                                        <input type="password"
                                               id="login_password"
                                               name="password"
                                               class="form-control"
                                               required>
                                    </div>

                                    {{-- Botão de submissão do formulário de login --}}
                                    <button type="submit" class="btn btn-primary w-100">
                                        Entrar
                                    </button>
                                </form>
                            </div>

                            {{-- Coluna de Cadastro --}}
                            <div class="col-md-6">
                                <h5>Cadastro</h5>
                                {{-- Formulário de cadastro --}}
                                <form method="POST" action="{{ route('auth.register') }}">
                                    {{-- Token CSRF obrigatório --}}
                                    @csrf

                                    {{-- Campo de nome --}}
                                    <div class="mb-3">
                                        <label for="reg_name" class="form-label">Nome</label>
                                        <input type="text"
                                               id="reg_name"
                                               name="name"
                                               class="form-control"
                                               required>
                                    </div>

                                    {{-- Campo de e-mail --}}
                                    <div class="mb-3">
                                        <label for="reg_email" class="form-label">E-mail</label>
                                        <input type="email"
                                               id="reg_email"
                                               name="email"
                                               class="form-control"
                                               required>
                                    </div>

                                    {{-- Campo de senha --}}
                                    <div class="mb-3">
                                        <label for="reg_password" class="form-label">Senha</label>
                                        <input type="password"
                                               id="reg_password"
                                               name="password"
                                               class="form-control"
                                               required>
                                    </div>

                                    {{-- Campo de confirmação de senha --}}
                                    <div class="mb-3">
                                        <label for="reg_password_confirmation" class="form-label">Confirmar Senha</label>
                                        <input type="password"
                                               id="reg_password_confirmation"
                                               name="password_confirmation"
                                               class="form-control"
                                               required>
                                    </div>

                                    {{-- Campo nome da empresa --}}
                                    <div class="mb-3">
                                        <label for="empresa_nome" class="form-label">Nome da Empresa</label>
                                        <input type="text"
                                               id="empresa_nome"
                                               name="empresa_nome"
                                               class="form-control"
                                               required>
                                    </div>

                                    {{-- Campo nível de acesso (role) --}}
                                    <input type="hidden" name="role" value="empresa">
                                    <div class="mb-3">
                                        <label class="form-label">Tipo de Usu�rio</label>
                                        <p class="form-control-plaintext">
                                            Empresa (dono/gestor) � funcion�rios entrar�o via convite.
                                        </p>
                                    </div>{{-- Botão de envio do cadastro --}}
                                    <button type="submit" class="btn btn-success w-100">
                                        Cadastrar
                                    </button>
                                </form>
                            </div>
                        </div> {{-- Fim da row com login e cadastro --}}
                    </div> {{-- Fim do card-body --}}
                </div> {{-- Fim do card --}}
            </div> {{-- Fim da coluna --}}
        </div> {{-- Fim da row --}}
    </div> {{-- Fim do container --}}

    {{-- Importa o JavaScript do Bootstrap para componentes funcionarem (opcional aqui) --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


