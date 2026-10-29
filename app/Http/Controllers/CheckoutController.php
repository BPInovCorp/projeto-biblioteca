<?php

namespace App\Http\Controllers;

use App\Models\Encomenda;
use App\Services\AuditoriaService;
use App\Services\CarrinhoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\InvalidRequestException;
use Stripe\Stripe;

class CheckoutController extends Controller
{
    protected CarrinhoService $carrinhoService;
    protected AuditoriaService $auditoriaService;

    public function __construct(
        CarrinhoService $carrinhoService,
        AuditoriaService $auditoriaService
    ) {
        $this->carrinhoService = $carrinhoService;
        $this->auditoriaService = $auditoriaService;
    }

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

        $dados = $request->validate([
            'nome_destinatario' => ['required', 'string', 'max:255'],
            'morada' => ['required', 'string', 'max:255'],
            'codigo_postal' => ['required', 'string', 'max:20'],
            'cidade' => ['required', 'string', 'max:100'],
            'pais' => ['required', 'string', 'max:100'],
            'telefone' => ['nullable', 'string', 'max:25'],
        ]);

        try {
            $encomenda = DB::transaction(function () use ($user, $dados) {
                $itens = $user->carrinhoItems()
                    ->with('livro')
                    ->lockForUpdate()
                    ->get();

                if ($itens->isEmpty()) {
                    throw new \InvalidArgumentException('O seu carrinho está vazio. Adicione livros antes de prosseguir.');
                }

                $totalCentimos = (int) $itens->sum(function ($item) {
                    return $item->quantidade * $item->livro->preco_centimos;
                });

                if ($totalCentimos <= 0) {
                    throw new \InvalidArgumentException('O valor total do carrinho é inválido para encomenda.');
                }

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
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('carrinho.index')->withErrors([
                'carrinho' => $e->getMessage(),
            ]);
        }

        session(['checkout_encomenda_id' => $encomenda->id]);

        $this->auditoriaService->registar(
            'encomendas',
            $encomenda->id,
            "iniciou a encomenda #{$encomenda->numero} no valor de {$encomenda->total_formatado}",
            $user
        );

        return redirect()->route('checkout.pagamento', $encomenda);
    }

    public function pagamento(Request $request, Encomenda $encomenda): RedirectResponse
    {
        abort_if($encomenda->user_id !== $request->user()->id, 403);

        if ($encomenda->estado_pagamento === Encomenda::PAGAMENTO_PAGO || $encomenda->estado_pagamento === 'pago') {
            return redirect()->route('checkout.sucesso', $encomenda);
        }

        $secretKey = config('services.stripe.secret');

        if (empty($secretKey)) {
            return redirect()->route('carrinho.index')->withErrors([
                'carrinho' => 'A chave secreta da Stripe não está configurada no ambiente.',
            ]);
        }

        Stripe::setApiKey($secretKey);

        try {
            $destinoUrl = DB::transaction(function () use ($encomenda, $request) {
                $encomendaTrancada = Encomenda::where('id', $encomenda->id)
                    ->lockForUpdate()
                    ->first();

                if (! empty($encomendaTrancada->stripe_session_id)) {
                    try {
                        $sessaoExistente = StripeSession::retrieve($encomendaTrancada->stripe_session_id);

                        if ($sessaoExistente->status === 'open' && ! empty($sessaoExistente->url)) {
                            return $sessaoExistente->url;
                        }

                        if ($sessaoExistente->payment_status === 'paid') {
                            return route('checkout.sucesso', $encomendaTrancada);
                        }
                    } catch (ApiConnectionException $e) {
                        Log::error("Erro de conexao com a Stripe na encomenda #{$encomendaTrancada->numero}: {$e->getMessage()}");
                        throw new RuntimeException('Não foi possível comunicar com o serviço de pagamentos. Por favor, tente novamente dentro de momentos.');
                    } catch (InvalidRequestException $e) {
                        Log::warning("Sessao Stripe anterior ({$encomendaTrancada->stripe_session_id}) inexistente ou expirada na Stripe: {$e->getMessage()}. A gerar nova sessao.");
                    } catch (ApiErrorException $e) {
                        Log::error("Erro na API da Stripe ao consultar sessao existente na encomenda #{$encomendaTrancada->numero}: {$e->getMessage()}");
                        throw new RuntimeException('Ocorreu um erro ao comunicar com a Stripe. Por favor, tente novamente dentro de momentos.');
                    }
                }

                $encomendaTrancada->load('items.livro');

                $lineItems = [];
                foreach ($encomendaTrancada->items as $item) {
                    $lineItems[] = [
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

                $session = StripeSession::create([
                    'line_items' => $lineItems,
                    'mode' => 'payment',
                    'customer_email' => $request->user()->email,
                    'client_reference_id' => (string) $encomendaTrancada->id,
                    'metadata' => [
                        'encomenda_id' => (string) $encomendaTrancada->id,
                        'encomenda_numero' => $encomendaTrancada->numero,
                    ],
                    'success_url' => route('checkout.sucesso', $encomendaTrancada) . '?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => route('checkout.cancelado', $encomendaTrancada),
                ]);

                $encomendaTrancada->update([
                    'stripe_session_id' => $session->id,
                ]);

                return $session->url;
            });
        } catch (RuntimeException $e) {
            return redirect()->route('carrinho.index')->withErrors([
                'carrinho' => $e->getMessage(),
            ]);
        }

        return redirect()->away($destinoUrl);
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

        $this->auditoriaService->registar(
            'encomendas',
            $encomenda->id,
            "cancelou a tentativa de pagamento da encomenda #{$encomenda->numero}",
            $request->user()
        );

        return view('checkout.cancelado', compact('encomenda'));
    }
}