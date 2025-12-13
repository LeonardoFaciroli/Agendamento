<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assinatura da Empresa</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('dashboard') }}">Controle de Diárias</a>
            <div class="d-flex ms-auto">
                <span class="navbar-text text-white me-3">
                    Logado como: {{ $user->name }} ({{ $user->role }})
                </span>
                <form method="POST" action="{{ route('auth.logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">Sair</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h3>Status da assinatura</h3>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm">Voltar</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
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

        <div class="row g-3">
            <div class="col-12 col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Empresa</h5>
                        <p class="mb-1"><strong>Nome:</strong> {{ $empresa->nome }}</p>
                        <p class="mb-1">
                            <strong>Status:</strong>
                            <span class="badge
                                @if($empresa->billing_status === 'active') bg-success
                                @elseif($empresa->billing_status === 'pending') bg-warning text-dark
                                @else bg-danger @endif">
                                {{ $empresa->billing_status ?? 'desconhecido' }}
                            </span>
                        </p>
                        <p class="mb-1">
                            <strong>Válido até:</strong>
                            {{ $empresa->paid_until ? \Carbon\Carbon::parse($empresa->paid_until)->format('d/m/Y') : '---' }}
                        </p>
                        <p class="mb-0"><strong>Plano:</strong> Mensal R$ {{ number_format($price / 100, 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Ação</h5>
                        @if ($isBillingManager)
                            <p>Inicie ou reative a assinatura via Mercado Pago. Valor mensal: <strong>R$ {{ number_format($price / 100, 2, ',', '.') }}</strong>.</p>
                            <form method="POST" action="{{ route('billing.create') }}" class="mb-3">
                                @csrf
                                <div class="mb-2">
                                    <label class="form-label">E-mail do pagador</label>
                                    <input type="email" name="payer_email" value="{{ old('payer_email', $user->email) }}" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Assinar via Mercado Pago</button>
                            </form>
                            @if ($empresa->mercadopago_preapproval_id)
                                <form method="POST" action="{{ route('billing.sync') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                                        Sincronizar status manualmente
                                    </button>
                                </form>
                            @endif
                        @else
                            <p class="text-muted">Somente a empresa/gerente pode iniciar ou reativar a assinatura.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
