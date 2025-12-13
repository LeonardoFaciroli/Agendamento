<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmpresaAtiva
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || ! $user->empresa) {
            return $next($request);
        }

        $empresa = $user->empresa;
        $status = $empresa->billing_status ?? 'past_due';
        $paidUntil = $empresa->paid_until ? Carbon::parse($empresa->paid_until) : null;

        $estaAtiva = $status === 'active' && (! $paidUntil || $paidUntil->isFuture() || $paidUntil->isToday());

        // Permitir acesso às páginas de billing e logout mesmo se inativa
        if (! $estaAtiva) {
            if ($request->routeIs('billing.*') || $request->routeIs('auth.logout')) {
                return $next($request);
            }

            return redirect()
                ->route('billing.index')
                ->with('error', 'Sua empresa está inadimplente ou sem assinatura. Regularize o pagamento para continuar.');
        }

        return $next($request);
    }
}
