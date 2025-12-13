<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class BillingController extends Controller
{
    protected int $price = 25000; // centavos (R$ 250,00)

    public function index()
    {
        $user = Auth::user();
        $empresa = $user->empresa;

        return view('billing.index', [
            'user' => $user,
            'empresa' => $empresa,
            'price' => $this->price,
            'isBillingManager' => $user->isEmpresa() || $user->isGerente(),
        ]);
    }

    public function createPreapproval(Request $request)
    {
        $user = Auth::user();
        $empresa = $user->empresa;

        if (! ($user->isEmpresa() || $user->isGerente())) {
            abort(403, 'Apenas empresa ou gerente podem criar a assinatura.');
        }

        $accessToken = config('services.mercadopago.access_token');
        if (! $accessToken) {
            return back()->withErrors(['general' => 'Configure MERCADOPAGO_ACCESS_TOKEN no .env.']);
        }

        $payerEmail = $request->input('payer_email', $user->email);

        $payload = [
            'reason' => 'Mensalidade Controle de Diarias',
            'auto_recurring' => [
                'frequency' => 1,
                'frequency_type' => 'months',
                'transaction_amount' => $this->price / 100,
                'currency_id' => 'BRL',
            ],
            'payer_email' => $payerEmail,
            'back_url' => route('billing.return'),
            'external_reference' => 'empresa:' . $empresa->id,
        ];

        $response = Http::withToken($accessToken)
            ->post('https://api.mercadopago.com/preapproval', $payload);

        if (! $response->successful()) {
            return back()->withErrors(['general' => 'Erro ao criar assinatura: ' . $response->body()]);
        }

        $data = $response->json();
        $preapprovalId = $data['id'] ?? null;
        $initPoint = $data['init_point'] ?? $data['sandbox_init_point'] ?? null;

        if (! $preapprovalId || ! $initPoint) {
            return back()->withErrors(['general' => 'Retorno inválido do Mercado Pago.']);
        }

        $empresa->update([
            'mercadopago_preapproval_id' => $preapprovalId,
            'mercadopago_payer_id' => $data['payer_id'] ?? $empresa->mercadopago_payer_id,
            'billing_status' => 'pending',
        ]);

        return redirect()->away($initPoint);
    }

    public function handleReturn()
    {
        return redirect()
            ->route('billing.index')
            ->with('success', 'Retornamos do Mercado Pago. Aguarde a confirmação da assinatura (webhook).');
    }

    /**
     * Atualiza empresa após confirmação manual (fallback) buscando status na API.
     */
    public function sync(Request $request)
    {
        $user = Auth::user();
        $empresa = $user->empresa;

        if (! ($user->isEmpresa() || $user->isGerente())) {
            abort(403);
        }

        if (! $empresa->mercadopago_preapproval_id) {
            return back()->withErrors(['general' => 'Nenhuma assinatura vinculada a esta empresa.']);
        }

        $accessToken = config('services.mercadopago.access_token');
        if (! $accessToken) {
            return back()->withErrors(['general' => 'Configure MERCADOPAGO_ACCESS_TOKEN no .env.']);
        }

        $detalhe = $this->buscarPreapproval($empresa->mercadopago_preapproval_id, $accessToken);

        if (! $detalhe) {
            return back()->withErrors(['general' => 'Não foi possível consultar a assinatura.']);
        }

        $this->atualizarEmpresaPorStatus($empresa, $detalhe);

        return back()->with('success', 'Status de cobrança atualizado.');
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
