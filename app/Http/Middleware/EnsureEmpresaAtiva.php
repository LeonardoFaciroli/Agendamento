<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmpresaAtiva
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->isAdmin()) {
            return $next($request);
        }

        if ($request->routeIs('billing.*') || $request->routeIs('auth.logout')) {
            return $next($request);
        }

        $empresa = $user->empresa;

        if (! $empresa) {
            return $next($request);
        }

        $ativo = $empresa->billing_status === 'active';
        $pago = $empresa->paid_until
            && ($empresa->paid_until->isToday() || $empresa->paid_until->isFuture());

        if ($ativo || $pago) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Assinatura pendente. Regularize para continuar.',
            ], 402);
        }

        return redirect()
            ->route('billing.index')
            ->withErrors(['general' => 'Assinatura pendente. Regularize para continuar.']);

    }
}
