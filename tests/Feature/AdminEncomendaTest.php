<?php

namespace Tests\Feature;

use App\Models\Editora;
use App\Models\Encomenda;
use App\Models\Livro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEncomendaTest extends TestCase
{
    use RefreshDatabase;

    private function criarLivro(string $nome = 'Obra Literária', string $preco = '20.00'): Livro
    {
        $editora = Editora::create([
            'nome' => 'Editora Central',
            'morada' => 'Rua Central, 1',
            'telefone' => '912345678',
            'nif' => '123456789',
            'email' => 'editora@central.pt',
        ]);

        return Livro::create([
            'editora_id' => $editora->id,
            'isbn' => '9789720049774',
            'nome' => $nome,
            'bibliografia' => 'Sinopse.',
            'preco' => $preco,
        ]);
    }

    private function criarEncomenda(User $user, string $numero, string $estadoPagamento = 'pago', int $total = 2000): Encomenda
    {
        $encomenda = Encomenda::create([
            'user_id' => $user->id,
            'numero' => $numero,
            'total_centimos' => $total,
            'estado' => Encomenda::ESTADO_PENDENTE,
            'estado_pagamento' => $estadoPagamento,
            'nome_destinatario' => 'Destinatário ' . $numero,
            'morada' => 'Morada ' . $numero,
            'codigo_postal' => '1000-001',
            'cidade' => 'Lisboa',
            'pais' => 'Portugal',
            'telefone' => '912345678',
            'stripe_session_id' => 'cs_test_' . $numero,
            'stripe_payment_intent_id' => 'pi_test_' . $numero,
        ]);

        $livro = $this->criarLivro('Livro ' . $numero, number_format($total / 100, 2, '.', ''));

        $encomenda->items()->create([
            'livro_id' => $livro->id,
            'quantidade' => 1,
            'preco_unitario_centimos' => $total,
        ]);

        return $encomenda;
    }

    public function test_apenas_administradores_podem_aceder_ao_painel_de_encomendas(): void
    {
        $this->get(route('admin.encomendas.index'))
            ->assertRedirect(route('login'));

        $cidadao = User::factory()->create(['role' => 'cidadao']);

        $this->actingAs($cidadao)
            ->get(route('admin.encomendas.index'))
            ->assertStatus(403);

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.encomendas.index'))
            ->assertOk();
    }

    public function test_administrador_pode_visualizar_listagem_de_encomendas(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $cidadao = User::factory()->create(['role' => 'cidadao']);

        $encomenda = $this->criarEncomenda($cidadao, 'ENC-20261027-TEST01', Encomenda::PAGAMENTO_PAGO, 3500);

        $this->actingAs($admin)
            ->get(route('admin.encomendas.index'))
            ->assertOk()
            ->assertSee('Gestão de Encomendas')
            ->assertSee('ENC-20261027-TEST01')
            ->assertSee('35,00 €')
            ->assertSee('Pago');
    }

    public function test_filtro_de_encomendas_pagas_retorna_apenas_encomendas_com_pagamento_concluido(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $cidadao = User::factory()->create(['role' => 'cidadao']);

        $this->criarEncomenda($cidadao, 'ENC-PAGA-999', Encomenda::PAGAMENTO_PAGO);
        $this->criarEncomenda($cidadao, 'ENC-PEND-111', Encomenda::PAGAMENTO_PENDENTE);

        $resposta = $this->actingAs($admin)
            ->get(route('admin.encomendas.index', ['filtro' => 'pagas']));

        $resposta->assertOk();
        $resposta->assertSee('ENC-PAGA-999');
        $resposta->assertDontSee('ENC-PEND-111');
    }

    public function test_filtro_de_encomendas_pendentes_retorna_apenas_encomendas_com_pagamento_pendente(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $cidadao = User::factory()->create(['role' => 'cidadao']);

        $this->criarEncomenda($cidadao, 'ENC-PAGA-999', Encomenda::PAGAMENTO_PAGO);
        $this->criarEncomenda($cidadao, 'ENC-PEND-111', Encomenda::PAGAMENTO_PENDENTE);

        $resposta = $this->actingAs($admin)
            ->get(route('admin.encomendas.index', ['filtro' => 'pendentes']));

        $resposta->assertOk();
        $resposta->assertSee('ENC-PEND-111');
        $resposta->assertDontSee('ENC-PAGA-999');
    }

    public function test_pesquisa_por_numero_nome_destinatario_ou_email_funciona_corretamente(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $cidadaoA = User::factory()->create(['name' => 'Carlos Silva', 'email' => 'carlos@exemplo.pt', 'role' => 'cidadao']);
        $cidadaoB = User::factory()->create(['name' => 'Mariana Lopes', 'email' => 'mariana@exemplo.pt', 'role' => 'cidadao']);

        $encA = $this->criarEncomenda($cidadaoA, 'ENC-ALPHA-01');
        $encB = $this->criarEncomenda($cidadaoB, 'ENC-BETA-02');

        $buscaNumero = $this->actingAs($admin)->get(route('admin.encomendas.index', ['search' => 'ALPHA']));
        $buscaNumero->assertSee($encA->numero)->assertDontSee($encB->numero);

        $buscaNome = $this->actingAs($admin)->get(route('admin.encomendas.index', ['search' => 'Mariana']));
        $buscaNome->assertSee($encB->numero)->assertDontSee($encA->numero);

        $buscaEmail = $this->actingAs($admin)->get(route('admin.encomendas.index', ['search' => 'carlos@exemplo.pt']));
        $buscaEmail->assertSee($encA->numero)->assertDontSee($encB->numero);
    }

    public function test_administrador_pode_visualizar_detalhes_completos_de_uma_encomenda(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $cidadao = User::factory()->create(['name' => 'Ana Martins', 'email' => 'ana@exemplo.pt', 'role' => 'cidadao']);

        $encomenda = $this->criarEncomenda($cidadao, 'ENC-AUDIT-777', Encomenda::PAGAMENTO_PAGO, 4000);

        $this->actingAs($admin)
            ->get(route('admin.encomendas.show', $encomenda))
            ->assertOk()
            ->assertSee('ENC-AUDIT-777')
            ->assertSee('Ana Martins')
            ->assertSee('ana@exemplo.pt')
            ->assertSee('cs_test_ENC-AUDIT-777')
            ->assertSee('pi_test_ENC-AUDIT-777')
            ->assertSee('Voltar às Encomendas');
    }

    public function test_precos_apresentados_no_detalhe_sao_historicos_e_imutaveis(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $cidadao = User::factory()->create(['role' => 'cidadao']);

        $livro = $this->criarLivro('Livro Preço Histórico', '12.00');

        $encomenda = Encomenda::create([
            'user_id' => $cidadao->id,
            'numero' => 'ENC-HISTORICO-123',
            'total_centimos' => 1200,
            'estado' => Encomenda::ESTADO_PENDENTE,
            'estado_pagamento' => Encomenda::PAGAMENTO_PAGO,
            'nome_destinatario' => 'Cidadão Teste',
            'morada' => 'Rua Histórica',
            'codigo_postal' => '1000-001',
            'cidade' => 'Lisboa',
            'pais' => 'Portugal',
        ]);

        $encomenda->items()->create([
            'livro_id' => $livro->id,
            'quantidade' => 1,
            'preco_unitario_centimos' => 1200,
        ]);

        $livro->update(['preco' => '99.99']);
        $this->assertEquals(9999, $livro->fresh()->preco_centimos);

        $this->actingAs($admin)
            ->get(route('admin.encomendas.show', $encomenda))
            ->assertOk()
            ->assertSee('12,00 €')
            ->assertDontSee('99,99 €');
    }
}