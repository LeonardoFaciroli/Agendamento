<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class MercadoPagoWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $accessToken = config('services.mercadopago.access_token');
        if (! $accessToken) {
            return response()->json(['error' => 'token não configurado'], 500);
        }

        $preapprovalId = $request->input('data.id')
            ?? $request->input('id')
            ?? $request->input('preapproval_id');

        if (! $preapprovalId) {
            return response()->json(['message' => 'preapproval_id ausente'], 200);
        }

        $detalhe = $this->buscarPreapproval($preapprovalId, $accessToken);
        if (! $detalhe) {
            return response()->json(['message' => 'não encontrado'], 200);
        }

        $empresa = Empresa::where('mercadopago_preapproval_id', $preapprovalId)->first();
        if (! $empresa) {
            return response()->json(['message' => 'empresa não localizada'], 200);
        }

        $this->atualizarEmpresaPorStatus($empresa, $detalhe);

        return response()->json(['ok' => true]);
    }

    protected function buscarPreapproval(string $id, string $accessToken): ?array
    {
        $resp = Http::withToken($accessToken)
            ->get("https://api.mercadopago.com/preapproval/{$id}");

        if (! $resp->successful()) {
            return null;
        }

        return $resp->json();
    }

    protected function atualizarEmpresaPorStatus(Empresa $empresa, array $detalhe): void
    {
        $status = $detalhe['status'] ?? null;
        $nextPaymentDate = $detalhe['next_payment_date'] ?? null;
        $payerId = $detalhe['payer_id'] ?? null;

        $paidUntil = null;
        if ($nextPaymentDate) {
            $paidUntil = Carbon::parse($nextPaymentDate);
        } elseif ($status === 'authorized') {
            $paidUntil = Carbon::now()->addMonth();
        }

        if ($status === 'authorized') {
            $empresa->billing_status = 'active';
        } elseif (in_array($status, ['cancelled', 'paused', 'rejected', 'expired'])) {
            $empresa->billing_status = 'canceled';
        } else {
            $empresa->billing_status = 'pending';
        }

        if ($paidUntil) {
            $empresa->paid_until = $paidUntil;
        }

        if ($payerId && ! $empresa->mercadopago_payer_id) {
            $empresa->mercadopago_payer_id = $payerId;
        }

        $empresa->save();
    }
}
