<?php

namespace Tests\Feature;

use App\Mail\LivroDisponivelMail;
use App\Models\Alerta;
use App\Models\Editora;
use App\Models\Livro;
use App\Models\Requisicao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AlertaDisponibilidadeTest extends TestCase
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

    private function criarAdmin(string $email = 'admin@teste.pt'): User
    {
        return User::forceCreate([
            'name' => 'Admin Teste',
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => 'admin',
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

    public function test_visitante_nao_autenticado_nao_pode_subscrever_alerta(): void
    {
        $livro = $this->criarLivro();

        $response = $this->post(route('alertas.store'), [
            'livro_id' => $livro->id,
        ]);

        $response->assertRedirect('/login');
        $this->assertEquals(0, Alerta::count());
    }

    public function test_cidadao_pode_subscrever_alerta_para_livro_indisponivel(): void
    {
        $cidadaoRequisitante = $this->criarCidadao('requisitante@teste.pt');
        $cidadaoInteressado = $this->criarCidadao('interessado@teste.pt');
        $livro = $this->criarLivro();

        Requisicao::create([
            'numero_sequencial' => 1,
            'user_id' => $cidadaoRequisitante->id,
            'livro_id' => $livro->id,
            'foto_cidadao' => $cidadaoRequisitante->profile_photo_url,
            'data_requisicao' => now()->toDateString(),
            'data_fim_prevista' => now()->addDays(5)->toDateString(),
            'status' => 'ativa',
        ]);

        $this->assertFalse($livro->fresh()->isDisponivel());

        $response = $this->actingAs($cidadaoInteressado)->post(route('alertas.store'), [
            'livro_id' => $livro->id,
        ]);

        $response->assertSessionHas('status');

        $this->assertDatabaseHas('alertas', [
            'user_id' => $cidadaoInteressado->id,
            'livro_id' => $livro->id,
            'notificado_at' => null,
        ]);
    }

    public function test_cidadao_nao_pode_subscrever_alerta_para_livro_ja_disponivel(): void
    {
        $cidadao = $this->criarCidadao();
        $livro = $this->criarLivro();

        $this->assertTrue($livro->isDisponivel());

        $response = $this->actingAs($cidadao)->post(route('alertas.store'), [
            'livro_id' => $livro->id,
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(0, Alerta::count());
    }

    public function test_cidadao_nao_pode_criar_duas_subscricoes_ativas_para_o_mesmo_livro(): void
    {
        $cidadaoRequisitante = $this->criarCidadao('requisitante@teste.pt');
        $cidadaoInteressado = $this->criarCidadao('interessado@teste.pt');
        $livro = $this->criarLivro();

        Requisicao::create([
            'numero_sequencial' => 1,
            'user_id' => $cidadaoRequisitante->id,
            'livro_id' => $livro->id,
            'foto_cidadao' => $cidadaoRequisitante->profile_photo_url,
            'data_requisicao' => now()->toDateString(),
            'data_fim_prevista' => now()->addDays(5)->toDateString(),
            'status' => 'ativa',
        ]);

        $this->actingAs($cidadaoInteressado)->post(route('alertas.store'), ['livro_id' => $livro->id]);
        $this->assertEquals(1, Alerta::count());

        $response = $this->actingAs($cidadaoInteressado)->post(route('alertas.store'), ['livro_id' => $livro->id]);
        $response->assertSessionHas('error');

        $this->assertEquals(1, Alerta::count());
    }

    public function test_devolucao_do_livro_dispara_emails_e_marca_alertas_como_notificados(): void
    {
        Mail::fake();

        $admin = $this->criarAdmin();
        $cidadaoRequisitante = $this->criarCidadao('dono@teste.pt');
        $cidadaoInteressado = $this->criarCidadao('alerta@teste.pt');
        $livro = $this->criarLivro();

        $requisicao = Requisicao::create([
            'numero_sequencial' => 1,
            'user_id' => $cidadaoRequisitante->id,
            'livro_id' => $livro->id,
            'foto_cidadao' => $cidadaoRequisitante->profile_photo_url,
            'data_requisicao' => now()->subDays(2)->toDateString(),
            'data_fim_prevista' => now()->addDays(3)->toDateString(),
            'status' => 'ativa',
        ]);

        $alerta = Alerta::create([
            'user_id' => $cidadaoInteressado->id,
            'livro_id' => $livro->id,
            'notificado_at' => null,
        ]);

        $response = $this->actingAs($admin)->post(route('requisicoes.devolver', $requisicao), [
            'data_devolucao' => now()->toDateString(),
        ]);

        $response->assertSessionHas('status');

        $this->assertTrue($livro->fresh()->isDisponivel());

        Mail::assertQueued(LivroDisponivelMail::class, function ($mail) use ($cidadaoInteressado, $livro) {
            return $mail->hasTo($cidadaoInteressado->email) && $mail->livro->id === $livro->id;
        });

        $alerta->refresh();
        $this->assertNotNull($alerta->notificado_at);
    }
}