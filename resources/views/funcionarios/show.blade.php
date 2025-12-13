{{-- resources/views/funcionarios/show.blade.php --}}

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>QR de Presença - {{ $funcionario->name }}</title>

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <style>
        body {
            background-color: #f4f4f4;
        }
        .qr-container {
            max-width: 600px;
            margin: 40px auto;
        }
    </style>
</head>
<body>
<div class="qr-container">
    <div class="card">
        <div class="card-header bg-dark text-white">
            QR Code de Presença
        </div>
        <div class="card-body text-center">
            <h5 class="card-title mb-3">
                {{ $funcionario->name }} ({{ $funcionario->role }})
            </h5>

            <p class="text-muted">
                Peça para o porteiro ou gerente apontar a câmera do celular para este QR
                para registrar sua entrada e saída.
            </p>

            @php
                // Gera a URL que será codificada no QR Code
                $urlQr = route('presenca.registrar_via_qr', ['token' => $funcionario->qr_token]);
            @endphp

            <div class="mb-3">
                {!! QrCode::size(260)->margin(1)->generate($urlQr) !!}
            </div>

            <p class="small text-muted">
                URL codificada no QR:
                <br>
                <code>{{ $urlQr }}</code>
            </p>

            <a href="{{ route('dashboard') }}" class="btn btn-secondary mt-3">
                Voltar ao Dashboard
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
