<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova senha</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header text-center">
                        <h4>Definir nova senha</h4>
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

                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">

                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail</label>
                                @if ($email)
                                    <input type="hidden" name="email" value="{{ $email }}">
                                    <input type="email"
                                           id="email"
                                           class="form-control"
                                           value="{{ $email }}"
                                           readonly>
                                @else
                                    <input type="email"
                                           id="email"
                                           name="email"
                                           class="form-control"
                                           value="{{ old('email') }}"
                                           required>
                                @endif
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Nova senha</label>
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
                                Atualizar senha
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
