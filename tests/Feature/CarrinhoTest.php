<?php

namespace Tests\Feature;

use App\Models\CarrinhoItem;
use App\Models\Editora;
use App\Models\Livro;
use App\Models\User;
use App\Services\CarrinhoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarrinhoTest extends TestCase
{
    use RefreshDatabase;

    private function criarLivro(string $nome = 'Livro Teste', string $preco = '15.00'): Livro
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
            'bibliografia' => 'Sinopse da obra de teste.',
            'preco' => $preco,
        ]);
    }

    public function test_cidadao_autenticado_pode_visualizar_carrinho_vazio(): void
    {
        $this->get(route('carrinho.index'))
            ->assertRedirect(route('login'));

        $user = User::factory()->create(['role' => 'cidadao']);

        $this->actingAs($user)
            ->get(route('carrinho.index'))
            ->assertOk()
            ->assertSee('O seu carrinho está vazio');
    }

    public function test_cidadao_pode_adicionar_livro_ao_carrinho(): void
    {
        $user = User::factory()->create(['role' => 'cidadao']);
        $livro = $this->criarLivro();

        $this->actingAs($user)
            ->post(route('carrinho.adicionar', $livro), [
                'quantidade' => 1,
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('carrinho_items', [
            'user_id' => $user->id,
            'livro_id' => $livro->id,
            'quantidade' => 1,
        ]);
    }

    public function test_adicionar_mesmo_livro_incrementa_quantidade(): void
    {
        $user = User::factory()->create(['role' => 'cidadao']);
        $livro = $this->criarLivro();

        $this->actingAs($user)->post(route('carrinho.adicionar', $livro), ['quantidade' => 1]);
        $this->actingAs($user)->post(route('carrinho.adicionar', $livro), ['quantidade' => 2]);

        $this->assertDatabaseCount('carrinho_items', 1);
        $this->assertDatabaseHas('carrinho_items', [
            'user_id' => $user->id,
            'livro_id' => $livro->id,
            'quantidade' => 3,
        ]);
    }

    public function test_cidadao_pode_atualizar_quantidade_de_um_item(): void
    {
        $user = User::factory()->create(['role' => 'cidadao']);
        $livro = $this->criarLivro();

        $item = CarrinhoItem::create([
            'user_id' => $user->id,
            'livro_id' => $livro->id,
            'quantidade' => 1,
        ]);

        $this->actingAs($user)
            ->patch(route('carrinho.atualizar', $item), [
                'quantidade' => 5,
            ])
            ->assertRedirect(route('carrinho.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('carrinho_items', [
            'id' => $item->id,
            'quantidade' => 5,
        ]);
    }

    public function test_atualizar_quantidade_para_zero_remove_o_item(): void
    {
        $user = User::factory()->create(['role' => 'cidadao']);
        $livro = $this->criarLivro();

        $item = CarrinhoItem::create([
            'user_id' => $user->id,
            'livro_id' => $livro->id,
            'quantidade' => 2,
        ]);

        $this->actingAs($user)
            ->patch(route('carrinho.atualizar', $item), [
                'quantidade' => 0,
            ])
            ->assertRedirect(route('carrinho.index'));

        $this->assertDatabaseMissing('carrinho_items', [
            'id' => $item->id,
        ]);
    }

    public function test_cidadao_pode_remover_artigo_do_carrinho(): void
    {
        $user = User::factory()->create(['role' => 'cidadao']);
        $livro = $this->criarLivro();

        $item = CarrinhoItem::create([
            'user_id' => $user->id,
            'livro_id' => $livro->id,
            'quantidade' => 1,
        ]);

        $this->actingAs($user)
            ->delete(route('carrinho.remover', $item))
            ->assertRedirect(route('carrinho.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('carrinho_items', [
            'id' => $item->id,
        ]);
    }

    public function test_cidadao_pode_limpar_a_totalidade_do_carrinho(): void
    {
        $user = User::factory()->create(['role' => 'cidadao']);
        $livro1 = $this->criarLivro('Livro 1');
        $livro2 = $this->criarLivro('Livro 2');

        CarrinhoItem::create(['user_id' => $user->id, 'livro_id' => $livro1->id, 'quantidade' => 1]);
        CarrinhoItem::create(['user_id' => $user->id, 'livro_id' => $livro2->id, 'quantidade' => 2]);

        $this->assertDatabaseCount('carrinho_items', 2);

        $this->actingAs($user)
            ->delete(route('carrinho.limpar'))
            ->assertRedirect(route('carrinho.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseCount('carrinho_items', 0);
    }

    public function test_utilizador_nao_pode_alterar_nem_remover_itens_de_outro_utilizador(): void
    {
        $userA = User::factory()->create(['role' => 'cidadao']);
        $userB = User::factory()->create(['role' => 'cidadao']);
        $livro = $this->criarLivro();

        $itemUserA = CarrinhoItem::create([
            'user_id' => $userA->id,
            'livro_id' => $livro->id,
            'quantidade' => 1,
        ]);

        $this->actingAs($userB)
            ->patch(route('carrinho.atualizar', $itemUserA), ['quantidade' => 10])
            ->assertStatus(403);

        $this->actingAs($userB)
            ->delete(route('carrinho.remover', $itemUserA))
            ->assertStatus(403);

        $this->assertDatabaseHas('carrinho_items', [
            'id' => $itemUserA->id,
            'user_id' => $userA->id,
            'quantidade' => 1,
        ]);
    }

    public function test_calculo_do_total_do_carrinho_e_rigoroso_com_precos_do_servidor(): void
    {
        $user = User::factory()->create(['role' => 'cidadao']);
        $livro1 = $this->criarLivro('Livro A', '10.50');
        $livro2 = $this->criarLivro('Livro B', '19.99');

        CarrinhoItem::create(['user_id' => $user->id, 'livro_id' => $livro1->id, 'quantidade' => 2]);
        CarrinhoItem::create(['user_id' => $user->id, 'livro_id' => $livro2->id, 'quantidade' => 1]);

        $service = app(CarrinhoService::class);
        $totalCentimos = $service->calcularTotalCentimos($user);

        $this->assertEquals(4099, $totalCentimos);

        $this->actingAs($user)
            ->get(route('carrinho.index'))
            ->assertOk()
            ->assertSee('40,99 €');
    }
}