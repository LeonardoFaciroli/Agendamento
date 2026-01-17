<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Diarias - Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header text-center">
                        <h4>Controle de Diarias - Login</h4>
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

                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('auth.login', [], false) }}">
                            @csrf

                            <div class="mb-3">
                                <label for="login_email" class="form-label">E-mail</label>
                                <input type="email"
                                       id="login_email"
                                       name="email"
                                       class="form-control"
                                       value="{{ old('email') }}"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="login_password" class="form-label">Senha</label>
                                <input type="password"
                                       id="login_password"
                                       name="password"
                                       class="form-control"
                                       required>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <a href="{{ route('password.request') }}" class="small">Esqueci minha senha</a>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                Entrar
                            </button>
                        </form>

                        <div class="mt-4 text-center">
                            <p class="text-muted mb-2">Cadastro apenas para diaristas</p>
                            <a href="{{ route('diaristas.register') }}" class="btn btn-outline-success w-100">
                                Cadastro
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
