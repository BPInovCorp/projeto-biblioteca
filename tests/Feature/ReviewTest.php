<?php

namespace Tests\Feature;

use App\Models\Comentario;
use App\Models\Editora;
use App\Models\Livro;
use App\Models\Requisicao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    private function criarCidadao(string $email = 'cidadao@teste.pt'): User
    {
        return User::forceCreate([
            'name' => 'Cidadão Teste',
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => 'cidadao',
        ]);
    }

    private function criarLivro(string $nome = 'Livro Teste'): Livro
    {
        $editora = Editora::create(['nome' => 'Editora Teste']);

        return Livro::create([
            'editora_id' => $editora->id,
            'isbn' => '9789890000001',
            'nome' => $nome,
            'preco' => '15.00',
        ]);
    }

    private function criarRequisicao(User $cidadao, Livro $livro, string $status = 'entregue'): Requisicao
    {
        return Requisicao::create([
            'numero_sequencial' => 1,
            'user_id' => $cidadao->id,
            'livro_id' => $livro->id,
            'foto_cidadao' => $cidadao->profile_photo_url,
            'data_requisicao' => now()->subDays(5)->toDateString(),
            'data_fim_prevista' => now()->toDateString(),
            'data_devolucao' => $status === 'entregue' ? now()->toDateString() : null,
            'dias_decorridos' => $status === 'entregue' ? 5 : null,
            'status' => $status,
        ]);
    }

    public function test_cidadao_pode_submeter_review_apos_livro_entregue(): void
    {
        $cidadao = $this->criarCidadao();
        $livro = $this->criarLivro();
        $requisicao = $this->criarRequisicao($cidadao, $livro, 'entregue');

        $response = $this->actingAs($cidadao)->post(route('comentarios.store'), [
            'requisicao_id' => $requisicao->id,
            'classificacao' => 5,
            'comentario' => 'Uma obra fantástica com excelente narrativa literária.',
        ]);

        $response->assertSessionHas('status');

        $this->assertDatabaseHas('reviews', [
            'user_id' => $cidadao->id,
            'livro_id' => $livro->id,
            'requisicao_id' => $requisicao->id,
            'classificacao' => 5,
            'status' => 'suspenso',
        ]);

        $comentario = Comentario::first();
        $this->assertNotNull($comentario);
        $this->assertEquals('Uma obra fantástica com excelente narrativa literária.', $comentario->comentario);
    }

    public function test_review_inicia_obrigatoriamente_com_status_suspenso(): void
    {
        $cidadao = $this->criarCidadao();
        $livro = $this->criarLivro();
        $requisicao = $this->criarRequisicao($cidadao, $livro, 'entregue');

        $this->actingAs($cidadao)->post(route('comentarios.store'), [
            'requisicao_id' => $requisicao->id,
            'classificacao' => 4,
            'comentario' => 'Muito interessante, recomendo a leitura.',
            'status' => 'ativo',
        ]);

        $comentario = Comentario::first();
        $this->assertNotNull($comentario);
        $this->assertEquals('suspenso', $comentario->status);
    }

    public function test_cidadao_nao_pode_submeter_review_em_requisicao_ativa(): void
    {
        $cidadao = $this->criarCidadao();
        $livro = $this->criarLivro();
        $requisicao = $this->criarRequisicao($cidadao, $livro, 'ativa');

        $response = $this->actingAs($cidadao)->post(route('comentarios.store'), [
            'requisicao_id' => $requisicao->id,
            'classificacao' => 5,
            'comentario' => 'Tentativa de avaliar livro ainda em empréstimo.',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(0, Comentario::count());
    }

    public function test_requisicao_nao_pode_ter_duas_reviews_duplicadas(): void
    {
        $cidadao = $this->criarCidadao();
        $livro = $this->criarLivro();
        $requisicao = $this->criarRequisicao($cidadao, $livro, 'entregue');

        $this->actingAs($cidadao)->post(route('comentarios.store'), [
            'requisicao_id' => $requisicao->id,
            'classificacao' => 5,
            'comentario' => 'Primeira avaliação submetida com sucesso.',
        ]);

        $this->assertEquals(1, Comentario::count());

        $response = $this->actingAs($cidadao)->post(route('comentarios.store'), [
            'requisicao_id' => $requisicao->id,
            'classificacao' => 4,
            'comentario' => 'Segunda tentativa para a mesma requisição.',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(1, Comentario::count());
    }

    public function test_cidadao_nao_pode_avaliar_requisicao_de_outro_cidadao(): void
    {
        $cidadaoDono = $this->criarCidadao('dono@teste.pt');
        $cidadaoInvasor = $this->criarCidadao('invasor@teste.pt');
        $livro = $this->criarLivro();
        $requisicao = $this->criarRequisicao($cidadaoDono, $livro, 'entregue');

        $response = $this->actingAs($cidadaoInvasor)->post(route('comentarios.store'), [
            'requisicao_id' => $requisicao->id,
            'classificacao' => 1,
            'comentario' => 'Tentativa não autorizada de avaliar a requisição de outro.',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(0, Comentario::count());
    }

    public function test_classificacao_fora_do_intervalo_e_rejeitada(): void
    {
        $cidadao = $this->criarCidadao();
        $livro = $this->criarLivro();
        $requisicao = $this->criarRequisicao($cidadao, $livro, 'entregue');

        $responseMaior = $this->actingAs($cidadao)->post(route('comentarios.store'), [
            'requisicao_id' => $requisicao->id,
            'classificacao' => 6,
            'comentario' => 'Comentário perfeitamente válido.',
        ]);
        $responseMaior->assertSessionHasErrors('classificacao');

        $responseMenor = $this->actingAs($cidadao)->post(route('comentarios.store'), [
            'requisicao_id' => $requisicao->id,
            'classificacao' => 0,
            'comentario' => 'Comentário perfeitamente válido.',
        ]);
        $responseMenor->assertSessionHasErrors('classificacao');

        $responseComentarioCurto = $this->actingAs($cidadao)->post(route('comentarios.store'), [
            'requisicao_id' => $requisicao->id,
            'classificacao' => 5,
            'comentario' => 'Ola',
        ]);
        $responseComentarioCurto->assertSessionHasErrors('comentario');

        $this->assertEquals(0, Comentario::count());
    }
}