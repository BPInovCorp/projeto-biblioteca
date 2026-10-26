<?php

namespace App\Http\Controllers;

use App\Models\Encomenda;
use App\Services\CarrinhoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

class CheckoutController extends Controller
{
    public function __construct(
        protected CarrinhoService $carrinhoService
    ) {}

    public function morada(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $itens = $this->carrinhoService->obterItens($user);

        if ($itens->isEmpty()) {
            return redirect()->route('carrinho.index')->withErrors([
                'carrinho' => 'O seu carrinho está vazio. Adicione livros antes de prosseguir.',
            ]);
        }

        $totalCentimos = $this->carrinhoService->calcularTotalCentimos($user);
        $totalItens = $this->carrinhoService->contarItens($user);

        return view('checkout.morada', compact('itens', 'totalCentimos', 'totalItens', 'user'));
    }

    public function guardarMorada(Request $request): RedirectResponse
    {
        $user = $request->user();
        $itens = $this->carrinhoService->obterItens($user);

        if ($itens->isEmpty()) {
            return redirect()->route('carrinho.index')->withErrors([
                'carrinho' => 'O seu carrinho está vazio. Adicione livros antes de prosseguir.',
            ]);
        }

        $dados = $request->validate([
            'nome_destinatario' => ['required', 'string', 'max:255'],
            'morada' => ['required', 'string', 'max:255'],
            'codigo_postal' => ['required', 'string', 'max:20'],
            'cidade' => ['required', 'string', 'max:100'],
            'pais' => ['required', 'string', 'max:100'],
            'telefone' => ['nullable', 'string', 'max:25'],
        ]);

        $totalCentimos = $this->carrinhoService->calcularTotalCentimos($user);

        if ($totalCentimos <= 0) {
            return redirect()->route('carrinho.index')->withErrors([
                'carrinho' => 'O valor total do carrinho é inválido para encomenda.',
            ]);
        }

        $encomenda = DB::transaction(function () use ($user, $dados, $itens, $totalCentimos) {
            do {
                $numero = 'ENC-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            } while (Encomenda::where('numero', $numero)->exists());

            $novaEncomenda = Encomenda::create([
                'user_id' => $user->id,
                'numero' => $numero,
                'total_centimos' => $totalCentimos,
                'estado' => Encomenda::ESTADO_PENDENTE,
                'estado_pagamento' => Encomenda::PAGAMENTO_PENDENTE,
                'nome_destinatario' => $dados['nome_destinatario'],
                'morada' => $dados['morada'],
                'codigo_postal' => $dados['codigo_postal'],
                'cidade' => $dados['cidade'],
                'pais' => $dados['pais'],
                'telefone' => $dados['telefone'] ?? null,
            ]);

            foreach ($itens as $item) {
                $novaEncomenda->items()->create([
                    'livro_id' => $item->livro_id,
                    'quantidade' => $item->quantidade,
                    'preco_unitario_centimos' => $item->livro->preco_centimos,
                ]);
            }

            return $novaEncomenda;
        });

        session(['checkout_encomenda_id' => $encomenda->id]);

        return redirect()->route('checkout.pagamento', $encomenda);
    }

    public function pagamento(request $request, encomenda $encomenda): redirectresponse
    {
        abort_if($encomenda->user_id !== $request->user()->id, 403);

        if ($encomenda->estado_pagamento === encomenda::PAGAMENTO_PAGO || $encomenda->estado_pagamento === 'pago') {
            return redirect()->route('checkout.sucesso', $encomenda);
        }

        $secretkey = config('services.stripe.secret');

        if (empty($secretkey)) {
            return redirect()->route('carrinho.index')->witherrors([
                'carrinho' => 'a chave secreta da stripe não está configurada no ambiente.',
            ]);
        }

        stripe::setapikey($secretkey);

        $encomenda->load('items.livro');

        $lineitems = [];
        foreach ($encomenda->items as $item) {
            $lineitems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $item->preco_unitario_centimos,
                    'product_data' => [
                        'name' => $item->livro->nome,
                    ],
                ],
                'quantity' => $item->quantidade,
            ];
        }

        $session = stripesession::create([
            'line_items' => $lineitems,
            'mode' => 'payment',
            'customer_email' => $request->user()->email,
            'client_reference_id' => (string) $encomenda->id,
            'metadata' => [
                'encomenda_id' => (string) $encomenda->id,
                'encomenda_numero' => $encomenda->numero,
            ],
            'success_url' => route('checkout.sucesso', $encomenda) . '?session_id={checkout_session_id}',
            'cancel_url' => route('checkout.cancelado', $encomenda),
        ]);

        $encomenda->update([
            'stripe_session_id' => $session->id,
        ]);

        return redirect()->away($session->url);
    }

    public function sucesso(request $request, encomenda $encomenda): view
    {
        abort_if($encomenda->user_id !== $request->user()->id, 403);

        $encomenda->load('items.livro');

        return view('checkout.sucesso', compact('encomenda'));
    }

    public function cancelado(request $request, encomenda $encomenda): view
    {
        abort_if($encomenda->user_id !== $request->user()->id, 403);

        return view('checkout.cancelado', compact('encomenda'));
    }
}