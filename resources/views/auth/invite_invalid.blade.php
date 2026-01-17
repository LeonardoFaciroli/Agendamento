<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convite invalido</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="alert alert-warning text-center">
                    <h5>Convite invalido ou expirado</h5>
                    <p class="mb-3">Entre em contato com o suporte para receber um novo convite.</p>
                    <a href="{{ route('login') }}" class="btn btn-outline-primary">Voltar ao login</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
