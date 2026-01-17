<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Convite</title>
</head>
<body>
    <p>Ola {{ $user->name }},</p>
    <p>Voce recebeu um convite para acessar o sistema de diaristas.</p>
    <p><strong>Empresa:</strong> {{ $empresa->nome }}</p>
    <p><strong>Filial:</strong> {{ $filial->nome }} @if ($filial->cidade) ({{ $filial->cidade }}) @endif</p>
    <p>Para ativar sua conta, clique no link abaixo:</p>
    <p><a href="{{ $acceptUrl }}">Ativar conta</a></p>
    <p>Se voce nao solicitou este acesso, ignore este email.</p>
</body>
</html>
