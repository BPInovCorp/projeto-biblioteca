<?php

namespace Tests\Feature;

use App\Mail\EstadoComentarioCidadaoMail;
use App\Mail\NovoComentarioAdminMail;
use App\Models\Comentario;
use App\Models\Editora;
use App\Models\Livro;
use App\Models\Requisicao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    private function criarAdmin(string $email = 'admin@teste.pt'): User
    {
        return User::forceCreate([
            'name' => 'Admin Teste',
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
    }

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

    public function test_submissao_de_review_envia_email_de_alerta_aos_administradores(): void
    {
        Mail::fake();

        $this->criarAdmin('admin1@teste.pt');
        $this->criarAdmin('admin2@teste.pt');
        $cidadao = $this->criarCidadao();
        $livro = $this->criarLivro();
        $requisicao = $this->criarRequisicao($cidadao, $livro, 'entregue');

        $this->actingAs($cidadao)->post(route('comentarios.store'), [
            'requisicao_id' => $requisicao->id,
            'classificacao' => 5,
            'comentario' => 'Obra marcante, excelente recomendação.',
        ]);

        Mail::assertQueued(NovoComentarioAdminMail::class, 2);
    }

    public function test_apenas_admin_pode_aceder_ao_painel_de_moderacao(): void
    {
        $this->get(route('admin.comentarios.index'))->assertRedirect('/login');

        $cidadao = $this->criarCidadao();
        $this->actingAs($cidadao)->get(route('admin.comentarios.index'))->assertStatus(403);

        $this->flushSession();

        $admin = $this->criarAdmin();
        $this->actingAs($admin)->get(route('admin.comentarios.index'))->assertOk();
    }

    public function test_admin_pode_aprovar_review_e_dispara_email_ao_cidadao(): void
    {
        Mail::fake();

        $admin = $this->criarAdmin();
        $cidadao = $this->criarCidadao();
        $livro = $this->criarLivro();
        $requisicao = $this->criarRequisicao($cidadao, $livro, 'entregue');

        $comentario = Comentario::create([
            'user_id' => $cidadao->id,
            'livro_id' => $livro->id,
            'requisicao_id' => $requisicao->id,
            'classificacao' => 5,
            'comentario' => 'Leitura extraordinária.',
            'status' => 'suspenso',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.comentarios.aprovar', $comentario));
        $response->assertSessionHas('status');

        $comentario->refresh();
        $this->assertEquals('ativo', $comentario->status);
        $this->assertNull($comentario->justificacao);

        Mail::assertQueued(EstadoComentarioCidadaoMail::class, function ($mail) use ($cidadao) {
            return $mail->hasTo($cidadao->email) && $mail->estado === 'ativo';
        });
    }

    public function test_admin_pode_recusar_review_com_justificacao_obrigatoria_e_dispara_email(): void
    {
        Mail::fake();

        $admin = $this->criarAdmin();
        $cidadao = $this->criarCidadao();
        $livro = $this->criarLivro();
        $requisicao = $this->criarRequisicao($cidadao, $livro, 'entregue');

        $comentario = Comentario::create([
            'user_id' => $cidadao->id,
            'livro_id' => $livro->id,
            'requisicao_id' => $requisicao->id,
            'classificacao' => 1,
            'comentario' => 'Texto desapropriado para teste.',
            'status' => 'suspenso',
        ]);

        $responseSemJustificacao = $this->actingAs($admin)->post(route('admin.comentarios.recusar', $comentario), [
            'justificacao' => '',
        ]);
        $responseSemJustificacao->assertSessionHasErrors('justificacao');

        $responseComJustificacao = $this->actingAs($admin)->post(route('admin.comentarios.recusar', $comentario), [
            'justificacao' => 'O comentário viola as normas de respeito comunitário.',
        ]);
        $responseComJustificacao->assertSessionHas('status');

        $comentario->refresh();
        $this->assertEquals('recusado', $comentario->status);
        $this->assertEquals('O comentário viola as normas de respeito comunitário.', $comentario->justificacao);

        Mail::assertQueued(EstadoComentarioCidadaoMail::class, function ($mail) use ($cidadao) {
            return $mail->hasTo($cidadao->email) && $mail->estado === 'recusado'
                && $mail->justificacao === 'O comentário viola as normas de respeito comunitário.';
        });
    }

    public function test_nao_e_possivel_re_moderar_review_ja_processada(): void
    {
        $admin = $this->criarAdmin();
        $cidadao = $this->criarCidadao();
        $livro = $this->criarLivro();
        $requisicao = $this->criarRequisicao($cidadao, $livro, 'entregue');

        $comentarioAtivo = Comentario::create([
            'user_id' => $cidadao->id,
            'livro_id' => $livro->id,
            'requisicao_id' => $requisicao->id,
            'classificacao' => 5,
            'comentario' => 'Avaliação já ativa.',
            'status' => 'ativo',
        ]);

        $responseAprovarNovamente = $this->actingAs($admin)->post(route('admin.comentarios.aprovar', $comentarioAtivo));
        $responseAprovarNovamente->assertSessionHas('error');

        $requisicao2 = Requisicao::create([
            'numero_sequencial' => 2,
            'user_id' => $cidadao->id,
            'livro_id' => $livro->id,
            'foto_cidadao' => $cidadao->profile_photo_url,
            'data_requisicao' => now()->subDays(6)->toDateString(),
            'data_fim_prevista' => now()->toDateString(),
            'data_devolucao' => now()->toDateString(),
            'dias_decorridos' => 6,
            'status' => 'entregue',
        ]);

        $comentarioRecusado = Comentario::create([
            'user_id' => $cidadao->id,
            'livro_id' => $livro->id,
            'requisicao_id' => $requisicao2->id,
            'classificacao' => 1,
            'comentario' => 'Avaliação já recusada.',
            'status' => 'recusado',
            'justificacao' => 'Motivo inicial.',
        ]);

        $responseRecusarNovamente = $this->actingAs($admin)->post(route('admin.comentarios.recusar', $comentarioRecusado), [
            'justificacao' => 'Novo motivo.',
        ]);
        $responseRecusarNovamente->assertSessionHas('error');
    }

    public function test_apenas_reviews_ativas_sao_apresentadas_no_catalogo_publico(): void
    {
        $cidadao = $this->criarCidadao();
        $livro = $this->criarLivro('Livro Com Reviews');

        $req1 = $this->criarRequisicao($cidadao, $livro, 'entregue');
        $req2 = Requisicao::create([
            'numero_sequencial' => 2,
            'user_id' => $cidadao->id,
            'livro_id' => $livro->id,
            'foto_cidadao' => $cidadao->profile_photo_url,
            'data_requisicao' => now()->subDays(10)->toDateString(),
            'data_fim_prevista' => now()->subDays(5)->toDateString(),
            'data_devolucao' => now()->subDays(5)->toDateString(),
            'dias_decorridos' => 5,
            'status' => 'entregue',
        ]);
        $req3 = Requisicao::create([
            'numero_sequencial' => 3,
            'user_id' => $cidadao->id,
            'livro_id' => $livro->id,
            'foto_cidadao' => $cidadao->profile_photo_url,
            'data_requisicao' => now()->subDays(15)->toDateString(),
            'data_fim_prevista' => now()->subDays(10)->toDateString(),
            'data_devolucao' => now()->subDays(10)->toDateString(),
            'dias_decorridos' => 5,
            'status' => 'entregue',
        ]);

        Comentario::create([
            'user_id' => $cidadao->id,
            'livro_id' => $livro->id,
            'requisicao_id' => $req1->id,
            'classificacao' => 5,
            'comentario' => 'Review Ativa Visivel Publicamente',
            'status' => 'ativo',
        ]);

        Comentario::create([
            'user_id' => $cidadao->id,
            'livro_id' => $livro->id,
            'requisicao_id' => $req2->id,
            'classificacao' => 4,
            'comentario' => 'Review Suspensa Totalmente Oculta',
            'status' => 'suspenso',
        ]);

        Comentario::create([
            'user_id' => $cidadao->id,
            'livro_id' => $livro->id,
            'requisicao_id' => $req3->id,
            'classificacao' => 1,
            'comentario' => 'Review Recusada Totalmente Oculta',
            'status' => 'recusado',
            'justificacao' => 'Spam',
        ]);

        $response = $this->get('/');
        $response->assertOk();

        $response->assertSee('Review Ativa Visivel Publicamente');
        $response->assertDontSee('Review Suspensa Totalmente Oculta');
        $response->assertDontSee('Review Recusada Totalmente Oculta');
    }
}