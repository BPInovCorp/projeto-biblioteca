<?php

namespace Tests\Feature;

use App\Models\CarrinhoItem;
use App\Models\Editora;
use App\Models\Encomenda;
use App\Models\Livro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CheckoutStripeTest extends TestCase
{
    use RefreshDatabase;

    private string $webhookSecret = 'whsec_test_secret_key_biblioteca_123456';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.stripe.webhook_secret' => $this->webhookSecret]);
        config(['services.stripe.secret' => 'sk_test_fake_secret_key']);
    }

    private function criarLivro(string $nome = 'Livro Teste', string $preco = '20.00'): Livro
    {
        $editora = Editora::create([
            'nome' => 'Editora Teste',
            'morada' => 'Rua Teste',
            'telefone' => '912345678',
            'nif' => '123456789',
            'email' => 'editora@teste.pt',
        ]);

        return Livro::create([
            'editora_id' => $editora->id,
            'isbn' => '9789720049774',
            'nome' => $nome,
            'bibliografia' => 'Sinopse da obra.',
            'preco' => $preco,
        ]);
    }

    private function gerarAssinaturaStripe(string $payload): string
    {
        $timestamp = time();
        $signedPayload = "{$timestamp}.{$payload}";
        $signature = hash_hmac('sha256', $signedPayload, $this->webhookSecret);

        return "t={$timestamp},v1={$signature}";
    }

    public function test_acesso_a_morada_redireciona_se_carrinho_estiver_vazio(): void
    {
        $user = User::factory()->create(['role' => 'cidadao']);

        $this->actingAs($user)
            ->get(route('checkout.morada'))
            ->assertRedirect(route('carrinho.index'))
            ->assertSessionHasErrors(['carrinho']);
    }

    public function test_cidadao_com_carrinho_pode_aceder_ao_formulario_de_morada(): void
    {
        $user = User::factory()->create(['role' => 'cidadao']);
        $livro = $this->criarLivro();

        CarrinhoItem::create([
            'user_id' => $user->id,
            'livro_id' => $livro->id,
            'quantidade' => 1,
        ]);

        $this->actingAs($user)
            ->get(route('checkout.morada'))
            ->assertOk()
            ->assertSee('Morada de Entrega')
            ->assertSee($user->name);
    }

    public function test_submeter_morada_valida_cria_encomenda_com_snapshot_de_precos(): void
    {
        $user = User::factory()->create(['role' => 'cidadao']);
        $livro = $this->criarLivro('Livro Compra', '25.50');

        CarrinhoItem::create([
            'user_id' => $user->id,
            'livro_id' => $livro->id,
            'quantidade' => 2,
        ]);

        $dadosMorada = [
            'nome_destinatario' => 'Bruno Pinto',
            'morada' => 'Av. da Liberdade, 100',
            'codigo_postal' => '1250-140',
            'cidade' => 'Lisboa',
            'pais' => 'Portugal',
            'telefone' => '912345678',
        ];

        $resposta = $this->actingAs($user)->post(route('checkout.guardar-morada'), $dadosMorada);

        $encomenda = Encomenda::where('user_id', $user->id)->first();
        $this->assertNotNull($encomenda);
        $this->assertEquals(5100, $encomenda->total_centimos);
        $this->assertEquals(Encomenda::ESTADO_PENDENTE, $encomenda->estado);
        $this->assertEquals(Encomenda::PAGAMENTO_PENDENTE, $encomenda->estado_pagamento);
        $this->assertStringStartsWith('ENC-', $encomenda->numero);

        $this->assertDatabaseHas('encomenda_items', [
            'encomenda_id' => $encomenda->id,
            'livro_id' => $livro->id,
            'quantidade' => 2,
            'preco_unitario_centimos' => 2550,
        ]);

        $resposta->assertRedirect(route('checkout.pagamento', $encomenda));
    }

    public function test_submeter_morada_com_dados_invalidos_falha_na_validacao(): void
    {
        $user = User::factory()->create(['role' => 'cidadao']);
        $livro = $this->criarLivro();

        CarrinhoItem::create([
            'user_id' => $user->id,
            'livro_id' => $livro->id,
            'quantidade' => 1,
        ]);

        $this->actingAs($user)
            ->post(route('checkout.guardar-morada'), [])
            ->assertSessionHasErrors(['nome_destinatario', 'morada', 'codigo_postal', 'cidade', 'pais']);

        $this->assertDatabaseCount('encomendas', 0);
    }

    public function test_utilizador_nao_pode_aceder_ao_checkout_de_encomenda_de_outro_cidadao(): void
    {
        $userA = User::factory()->create(['role' => 'cidadao']);
        $userB = User::factory()->create(['role' => 'cidadao']);

        $encomendaUserA = Encomenda::create([
            'user_id' => $userA->id,
            'numero' => 'ENC-20261027-AAAAAA',
            'total_centimos' => 2000,
            'estado' => Encomenda::ESTADO_PENDENTE,
            'estado_pagamento' => Encomenda::PAGAMENTO_PENDENTE,
            'nome_destinatario' => 'User A',
            'morada' => 'Rua A',
            'codigo_postal' => '1000-001',
            'cidade' => 'Lisboa',
            'pais' => 'Portugal',
        ]);

        $this->actingAs($userB)
            ->get(route('checkout.pagamento', $encomendaUserA))
            ->assertStatus(403);

        $this->actingAs($userB)
            ->get(route('checkout.sucesso', $encomendaUserA))
            ->assertStatus(403);

        $this->actingAs($userB)
            ->get(route('checkout.cancelado', $encomendaUserA))
            ->assertStatus(403);
    }

    public function test_webhook_rejeita_pedido_com_assinatura_invalida(): void
    {
        $payload = json_encode(['id' => 'evt_fake']);

        $this->call('POST', route('stripe.webhook'), [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => 't=123456,v1=assinatura_falsa',
            'CONTENT_TYPE' => 'application/json',
        ], $payload)->assertStatus(400);
    }

    public function test_webhook_processa_checkout_session_completed_com_sucesso(): void
    {
        $user = User::factory()->create(['role' => 'cidadao']);
        $livro = $this->criarLivro('Obra Confirmada', '15.00');

        $carrinhoItem = CarrinhoItem::create([
            'user_id' => $user->id,
            'livro_id' => $livro->id,
            'quantidade' => 1,
        ]);

        $encomenda = Encomenda::create([
            'user_id' => $user->id,
            'numero' => 'ENC-20261027-BBBBBB',
            'total_centimos' => 1500,
            'estado' => Encomenda::ESTADO_PENDENTE,
            'estado_pagamento' => Encomenda::PAGAMENTO_PENDENTE,
            'nome_destinatario' => 'Bruno Pinto',
            'morada' => 'Rua B',
            'codigo_postal' => '1000-002',
            'cidade' => 'Lisboa',
            'pais' => 'Portugal',
        ]);

        $encomenda->items()->create([
            'livro_id' => $livro->id,
            'quantidade' => 1,
            'preco_unitario_centimos' => 1500,
        ]);

        $eventId = 'evt_test_' . uniqid();
        $paymentIntentId = 'pi_test_' . uniqid();

        $payload = json_encode([
            'id' => $eventId,
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_' . uniqid(),
                    'payment_status' => 'paid',
                    'client_reference_id' => (string) $encomenda->id,
                    'amount_total' => 1500,
                    'currency' => 'eur',
                    'payment_intent' => $paymentIntentId,
                    'metadata' => [
                        'encomenda_id' => (string) $encomenda->id,
                    ],
                ],
            ],
        ]);

        $assinatura = $this->gerarAssinaturaStripe($payload);

        $resposta = $this->call('POST', route('stripe.webhook'), [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => $assinatura,
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $resposta->assertStatus(200);

        $encomenda->refresh();
        $this->assertEquals(Encomenda::PAGAMENTO_PAGO, $encomenda->estado_pagamento);
        $this->assertEquals($paymentIntentId, $encomenda->stripe_payment_intent_id);

        $this->assertDatabaseMissing('carrinho_items', [
            'id' => $carrinhoItem->id,
        ]);

        $this->assertDatabaseHas('stripe_webhook_events', [
            'event_id' => $eventId,
            'tipo' => 'checkout.session.completed',
        ]);
    }

    public function test_webhook_e_idempotente_rejeitando_duplicados_com_sucesso(): void
    {
        $user = User::factory()->create(['role' => 'cidadao']);
        $livro = $this->criarLivro();

        $encomenda = Encomenda::create([
            'user_id' => $user->id,
            'numero' => 'ENC-20261027-CCCCCC',
            'total_centimos' => 2000,
            'estado' => Encomenda::ESTADO_PENDENTE,
            'estado_pagamento' => Encomenda::PAGAMENTO_PENDENTE,
            'nome_destinatario' => 'Bruno Pinto',
            'morada' => 'Rua C',
            'codigo_postal' => '1000-003',
            'cidade' => 'Lisboa',
            'pais' => 'Portugal',
        ]);

        $encomenda->items()->create([
            'livro_id' => $livro->id,
            'quantidade' => 1,
            'preco_unitario_centimos' => 2000,
        ]);

        $eventId = 'evt_test_idempotente_' . uniqid();

        $payload = json_encode([
            'id' => $eventId,
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_' . uniqid(),
                    'payment_status' => 'paid',
                    'client_reference_id' => (string) $encomenda->id,
                    'amount_total' => 2000,
                    'currency' => 'eur',
                    'payment_intent' => 'pi_test_123',
                    'metadata' => [
                        'encomenda_id' => (string) $encomenda->id,
                    ],
                ],
            ],
        ]);

        $assinatura = $this->gerarAssinaturaStripe($payload);

        $primeiroPedido = $this->call('POST', route('stripe.webhook'), [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => $assinatura,
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $segundoPedido = $this->call('POST', route('stripe.webhook'), [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => $assinatura,
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $primeiroPedido->assertStatus(200);
        $segundoPedido->assertStatus(200);

        $this->assertEquals(1, DB::table('stripe_webhook_events')->where('event_id', $eventId)->count());
    }

    public function test_webhook_ignora_sessao_com_pagamento_nao_concluido(): void
    {
        $user = User::factory()->create(['role' => 'cidadao']);
        $livro = $this->criarLivro();

        $carrinhoItem = CarrinhoItem::create([
            'user_id' => $user->id,
            'livro_id' => $livro->id,
            'quantidade' => 1,
        ]);

        $encomenda = Encomenda::create([
            'user_id' => $user->id,
            'numero' => 'ENC-20261027-DDDDDD',
            'total_centimos' => 2000,
            'estado' => Encomenda::ESTADO_PENDENTE,
            'estado_pagamento' => Encomenda::PAGAMENTO_PENDENTE,
            'nome_destinatario' => 'Bruno Pinto',
            'morada' => 'Rua D',
            'codigo_postal' => '1000-004',
            'cidade' => 'Lisboa',
            'pais' => 'Portugal',
        ]);

        $encomenda->items()->create([
            'livro_id' => $livro->id,
            'quantidade' => 1,
            'preco_unitario_centimos' => 2000,
        ]);

        $payload = json_encode([
            'id' => 'evt_test_unpaid_' . uniqid(),
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_' . uniqid(),
                    'payment_status' => 'unpaid',
                    'client_reference_id' => (string) $encomenda->id,
                    'amount_total' => 2000,
                    'currency' => 'eur',
                ],
            ],
        ]);

        $assinatura = $this->gerarAssinaturaStripe($payload);

        $this->call('POST', route('stripe.webhook'), [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => $assinatura,
            'CONTENT_TYPE' => 'application/json',
        ], $payload)->assertStatus(200);

        $encomenda->refresh();
        $this->assertEquals(Encomenda::PAGAMENTO_PENDENTE, $encomenda->estado_pagamento);
        $this->assertDatabaseHas('carrinho_items', ['id' => $carrinhoItem->id]);
    }

    public function test_ecras_de_sucesso_e_cancelamento_apresentam_estados_corretos(): void
    {
        $user = User::factory()->create(['role' => 'cidadao']);
        $livro = $this->criarLivro('Obra de Sucesso', '30.00');

        $encomendaPendente = Encomenda::create([
            'user_id' => $user->id,
            'numero' => 'ENC-20261027-EEEEEE',
            'total_centimos' => 3000,
            'estado' => Encomenda::ESTADO_PENDENTE,
            'estado_pagamento' => Encomenda::PAGAMENTO_PENDENTE,
            'nome_destinatario' => 'Bruno Pinto',
            'morada' => 'Rua E',
            'codigo_postal' => '1000-005',
            'cidade' => 'Lisboa',
            'pais' => 'Portugal',
        ]);

        $encomendaPendente->items()->create([
            'livro_id' => $livro->id,
            'quantidade' => 1,
            'preco_unitario_centimos' => 3000,
        ]);

        $this->actingAs($user)
            ->get(route('checkout.sucesso', $encomendaPendente))
            ->assertOk()
            ->assertSee('A Processar Confirmação')
            ->assertSee('ENC-20261027-EEEEEE');

        $encomendaPendente->update(['estado_pagamento' => Encomenda::PAGAMENTO_PAGO]);

        $this->actingAs($user)
            ->get(route('checkout.sucesso', $encomendaPendente))
            ->assertOk()
            ->assertSee('Encomenda Confirmada com Sucesso!')
            ->assertSee('Pagamento Concluído');

        $this->actingAs($user)
            ->get(route('checkout.cancelado', $encomendaPendente))
            ->assertOk()
            ->assertSee('O processo de pagamento foi cancelado')
            ->assertSee('Tentar Novamente o Pagamento');
    }
}