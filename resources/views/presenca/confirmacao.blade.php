<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Presença Registrada</title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card">
        <div class="card-header bg-success text-white">
            Presença registrada
        </div>
        <div class="card-body">
            <h5 class="card-title">{{ $funcionario->name }}</h5>
            <p class="card-text">
                {{ $mensagem }}<br>
                Data: {{ $registro->data_presenca->format('d/m/Y') }}<br>
                Entrada: {{ $registro->hora_entrada ?? '-' }}<br>
                Saída: {{ $registro->hora_saida ?? '-' }}<br>
                Horas trabalhadas: {{ $registro->horas_trabalhadas ?? '-' }}
            </p>
            <a href="{{ route('presenca.scanner') }}" class="btn btn-primary">
                Ler outro QR
            </a>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                Voltar ao Dashboard
            </a>
        </div>
    </div>
</div>
</body>
</html>
