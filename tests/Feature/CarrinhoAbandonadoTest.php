<?php

namespace Tests\Feature;

use App\Mail\CarrinhoAbandonadoMail;
use App\Models\CarrinhoItem;
use App\Models\Editora;
use App\Models\Encomenda;
use App\Models\Livro;
use App\Models\User;
use App\Services\CarrinhoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CarrinhoAbandonadoTest extends TestCase
{
    use RefreshDatabase;

    private function criarLivro(string $nome = 'Obra Abandonada', string $preco = '18.00'): Livro
    {
        $editora = Editora::create([
            'nome' => 'Editora Lusitana',
            'morada' => 'Rua das Flores, 10',
            'telefone' => '912345678',
            'nif' => '123456789',
            'email' => 'editora@lusitana.pt',
        ]);

        return Livro::create([
            'editora_id' => $editora->id,
            'isbn' => '9789720049774',
            'nome' => $nome,
            'bibliografia' => 'Sinopse explicativa.',
            'preco' => $preco,
        ]);
    }

    public function test_comando_nao_dispara_lembrete_se_carrinho_foi_atualizado_ha_menos_de_uma_hora(): void
    {
        Mail::fake();

        $user = User::factory()->create(['role' => 'cidadao']);
        $livro = $this->criarLivro();

        CarrinhoItem::create([
            'user_id' => $user->id,
            'livro_id' => $livro->id,
            'quantidade' => 1,
        ]);

        $this->travel(50)->minutes();

        $this->artisan('biblioteca:lembrete-carrinho-abandonado')
            ->assertSuccessful();

        Mail::assertNothingQueued();

        $this->assertDatabaseHas('carrinho_items', [
            'user_id' => $user->id,
            'aviso_abandono_enviado_em' => null,
        ]);
    }

    public function test_comando_dispara_lembrete_quando_carrinho_esta_inativo_ha_mais_de_uma_hora(): void
    {
        Mail::fake();

        $user = User::factory()->create(['role' => 'cidadao']);
        $livro = $this->criarLivro();

        $item = CarrinhoItem::create([
            'user_id' => $user->id,
            'livro_id' => $livro->id,
            'quantidade' => 1,
        ]);

        $this->travel(70)->minutes();

        $this->artisan('biblioteca:lembrete-carrinho-abandonado')
            ->assertSuccessful();

        Mail::assertQueued(CarrinhoAbandonadoMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        $this->assertNotNull($item->fresh()->aviso_abandono_enviado_em);
    }

    public function test_comando_e_idempotente_nao_enviando_lembretes_duplicados(): void
    {
        Mail::fake();

        $user = User::factory()->create(['role' => 'cidadao']);
        $livro = $this->criarLivro();

        CarrinhoItem::create([
            'user_id' => $user->id,
            'livro_id' => $livro->id,
            'quantidade' => 1,
        ]);

        $this->travel(2)->hours();

        $this->artisan('biblioteca:lembrete-carrinho-abandonado')->assertSuccessful();
        $this->artisan('biblioteca:lembrete-carrinho-abandonado')->assertSuccessful();

        Mail::assertQueued(CarrinhoAbandonadoMail::class, 1);
    }

    public function test_nova_atividade_no_carrinho_rearma_o_ciclo_de_lembrete(): void
    {
        $user = User::factory()->create(['role' => 'cidadao']);
        $livro1 = $this->criarLivro('Livro 1');
        $livro2 = $this->criarLivro('Livro 2');

        $item = CarrinhoItem::create([
            'user_id' => $user->id,
            'livro_id' => $livro1->id,
            'quantidade' => 1,
            'aviso_abandono_enviado_em' => now()->subHours(3),
        ]);

        $service = app(CarrinhoService::class);
        $service->adicionarLivro($user, $livro2, 1);

        $this->assertNull($user->carrinhoItems()->where('livro_id', $livro2->id)->first()->aviso_abandono_enviado_em);

        $service->atualizarQuantidade($user, $item->id, 2);
        $this->assertNull($item->fresh()->aviso_abandono_enviado_em);

        Mail::fake();

        $this->travel(70)->minutes();

        $this->artisan('biblioteca:lembrete-carrinho-abandonado')->assertSuccessful();

        Mail::assertQueued(CarrinhoAbandonadoMail::class, 1);
    }

    public function test_utilizador_com_encomenda_recente_nao_recebe_aviso_de_abandono(): void
    {
        Mail::fake();

        $user = User::factory()->create(['role' => 'cidadao']);
        $livro = $this->criarLivro();

        CarrinhoItem::create([
            'user_id' => $user->id,
            'livro_id' => $livro->id,
            'quantidade' => 1,
        ]);

        $this->travel(2)->hours();

        Encomenda::create([
            'user_id' => $user->id,
            'numero' => 'ENC-RECENTE-111',
            'total_centimos' => 1800,
            'estado' => Encomenda::ESTADO_CONCLUIDA,
            'estado_pagamento' => Encomenda::PAGAMENTO_PAGO,
            'nome_destinatario' => 'Bruno Pinto',
            'morada' => 'Rua Recente',
            'codigo_postal' => '1000-001',
            'cidade' => 'Lisboa',
            'pais' => 'Portugal',
            'updated_at' => now()->subMinutes(15),
        ]);

        $this->artisan('biblioteca:lembrete-carrinho-abandonado')->assertSuccessful();

        Mail::assertNothingQueued();
    }

    public function test_mailable_contem_dados_corretos_do_cidadao_e_dos_livros(): void
    {
        $user = User::factory()->create(['name' => 'Bruno Pinto', 'role' => 'cidadao']);
        $livro = $this->criarLivro('Livro Especial', '22.50');

        $item = CarrinhoItem::create([
            'user_id' => $user->id,
            'livro_id' => $livro->id,
            'quantidade' => 2,
        ]);

        $itens = $user->carrinhoItems()->with(['livro.autores'])->get();

        $mailable = new CarrinhoAbandonadoMail($user, $itens, 4500);
        $html = $mailable->render();

        $this->assertStringContainsString('Olá, Bruno Pinto!', $html);
        $this->assertStringContainsString('Livro Especial', $html);
        $this->assertStringContainsString('45,00 €', $html);
        $this->assertStringContainsString(route('carrinho.index'), $html);
        $this->assertStringContainsString('Retomar o Meu Carrinho', $html);
    }
}