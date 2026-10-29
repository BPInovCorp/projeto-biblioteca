<?php

namespace App\Http\Controllers;

use App\Models\Encomenda;
use App\Services\AuditoriaService;
use App\Services\CarrinhoService;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Refund;
use Stripe\Stripe;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function __construct(
        protected CarrinhoService $carrinhoService,
        protected AuditoriaService $auditoriaService
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        if (empty($webhookSecret)) {
            return response()->json(['erro' => 'Segredo do webhook não configurado no servidor.'], 500);
        }

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (UnexpectedValueException $e) {
            return response()->json(['erro' => 'Payload do webhook inválido.'], 400);
        } catch (SignatureVerificationException $e) {
            return response()->json(['erro' => 'Assinatura criptográfica inválida.'], 400);
        }

        $jaProcessado = DB::table('stripe_webhook_events')
            ->where('event_id', $event->id)
            ->exists();

        if ($jaProcessado) {
            return response()->json(['status' => 'evento_ja_processado'], 200);
        }

        if (in_array($event->type, ['checkout.session.completed', 'checkout.session.async_payment_succeeded'])) {
            $session = $event->data->object;

            if ($session->payment_status !== 'paid') {
                return response()->json(['status' => 'pagamento_nao_concluido'], 200);
            }

            $encomendaId = $session->client_reference_id ?? $session->metadata->encomenda_id ?? null;

            if (! $encomendaId) {
                return response()->json(['erro' => 'Identificador da encomenda ausente na sessão.'], 400);
            }

            try {
                DB::transaction(function () use ($event, $session, $encomendaId) {
                    $encomenda = Encomenda::where('id', $encomendaId)
                        ->lockForUpdate()
                        ->first();

                    if (! $encomenda) {
                        throw new Exception('Encomenda não encontrada para o identificador fornecido.');
                    }

                    if ((int) $session->amount_total !== (int) $encomenda->total_centimos) {
                        throw new Exception('O montante da sessão não corresponde ao total da encomenda.');
                    }

                    if (strtolower((string) $session->currency) !== 'eur') {
                        throw new Exception('A moeda da sessão Stripe não é EUR.');
                    }

                    if ($encomenda->estado_pagamento === Encomenda::PAGAMENTO_PAGO) {
                        if (! empty($session->payment_intent) && $encomenda->stripe_payment_intent_id !== $session->payment_intent) {
                            Stripe::setApiKey(config('services.stripe.secret'));

                            try {
                                    Refund::create([
                                        'payment_intent' => $session->payment_intent,
                                        'reason' => 'duplicate',
                                    ], [
                                        'idempotency_key' => 'refund_' . $session->payment_intent,
                                    ]);
                                    Log::info("Reembolso de pagamento duplicado executado com sucesso para a encomenda #{$encomenda->numero}, intent: {$session->payment_intent}");

                                    $this->auditoriaService->registar(
                                        'Encomendas',
                                        $encomenda->id,
                                        "Processou reembolso automático de cobrança duplicada da encomenda #{$encomenda->numero} na Stripe",
                                        $encomenda->user
                                    );
                                } catch (Exception $e) {
                                Log::error("Falha ao emitir reembolso de pagamento duplicado para a encomenda #{$encomenda->numero}, intent: {$session->payment_intent}. Erro: {$e->getMessage()}");
                                throw new Exception("Falha no reembolso de cobranca duplicada. O webhook sera repetido pela Stripe: " . $e->getMessage());
                            }
                        }
                    } else {
                        if (! empty($encomenda->stripe_session_id) && $session->id !== $encomenda->stripe_session_id) {
                            Log::warning("Sessao Stripe recebida ({$session->id}) difere da sessao ativa ({$encomenda->stripe_session_id}) na encomenda #{$encomenda->numero}. Pagamento honrado e registo atualizado.");
                        }

                        $encomenda->update([
                            'estado_pagamento' => Encomenda::PAGAMENTO_PAGO,
                            'stripe_session_id' => $session->id,
                            'stripe_payment_intent_id' => $session->payment_intent ?? null,
                        ]);

                        $user = $encomenda->user;
                        if ($user) {
                            $encomenda->load('items');
                            $itensComprados = $encomenda->items->pluck('quantidade', 'livro_id')->toArray();
                            $this->carrinhoService->removerItensComprados($user, $itensComprados);
                        }

                        $this->auditoriaService->registar(
                            'Encomendas',
                            $encomenda->id,
                            "Confirmou o pagamento da encomenda #{$encomenda->numero} via Stripe Checkout",
                            $encomenda->user
                        );
                    }

                    DB::table('stripe_webhook_events')->insert([
                        'event_id' => $event->id,
                        'tipo' => $event->type,
                        'payload' => json_encode([
                            'session_id' => $session->id,
                            'encomenda_id' => $encomenda->id,
                        ]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                });
            } catch (QueryException $e) {
                if (str_contains($e->getMessage(), 'Duplicate entry') || $e->getCode() === '23000') {
                    return response()->json(['status' => 'evento_duplicado_ignorado'], 200);
                }

                return response()->json(['erro' => 'Erro de base de dados ao processar transação.'], 500);
            } catch (Exception $e) {
                return response()->json(['erro' => $e->getMessage()], 500);
            }
        }

        return response()->json(['status' => 'sucesso'], 200);
    }
}