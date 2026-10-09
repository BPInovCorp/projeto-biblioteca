<?php

namespace Tests\Feature;

use App\Mail\LembreteDevolucaoMail;
use App\Mail\RequisicaoConfirmadaMail;
use App\Models\Editora;
use App\Models\Livro;
use App\Models\Requisicao;
use App\Models\User;
use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RequisicaoTest extends TestCase
{
    use RefreshDatabase;

    private function criarLivro(string $nome = 'Livro Teste'): Livro
    {
        $editora = Editora::create([
            'nome' => 'Editora Teste',
        ]);

        return Livro::create([
            'editora_id' => $editora->id,
            'isbn' => '978-972-0-00001-0',
            'nome' => $nome,
            'preco' => '15.00',
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

    private function criarAdmin(string $email = 'admin@teste.pt'): User
    {
        return User::forceCreate([
            'name' => 'Admin Teste',
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
    }

    public function test_registo_publico_atribui_sempre_papel_de_cidadao(): void
    {
        $response = $this->post('/register', [
            'name' => 'Novo Utilizador',
            'email' => 'novo@teste.pt',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertAuthenticated();
        $user = User::where('email', 'novo@teste.pt')->first();
        $this->assertNotNull($user);
        $this->assertEquals('cidadao', $user->role);
        $this->assertTrue($user->isCidadao());
        $this->assertFalse($user->isAdmin());
    }

    public function test_cidadao_nao_pode_criar_outro_admin(): void
    {
        $cidadao = $this->criarCidadao();

        $response = $this->actingAs($cidadao)->post(route('admin.users.store'), [
            'name' => 'Outro Admin',
            'email' => 'outro@admin.pt',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_pode_criar_outro_admin(): void
    {
        $admin = $this->criarAdmin();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Outro Admin',
            'email' => 'outro@admin.pt',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('admin.users.create'));

        $novoAdmin = User::where('email', 'outro@admin.pt')->first();
        $this->assertNotNull($novoAdmin);
        $this->assertEquals('admin', $novoAdmin->role);
        $this->assertTrue($novoAdmin->isAdmin());
    }

    public function test_cidadao_pode_requisitar_livro_disponivel_e_recebe_email(): void
    {
        Mail::fake();

        $cidadao = $this->criarCidadao();
        $this->criarAdmin();
        $livro = $this->criarLivro();

        $response = $this->actingAs($cidadao)->post(route('requisicoes.store'), [
            'livro_id' => $livro->id,
        ]);

        $response->assertSessionHas('status');

        $this->assertDatabaseHas('requisicoes', [
            'user_id' => $cidadao->id,
            'livro_id' => $livro->id,
            'numero_sequencial' => 1,
            'status' => 'ativa',
        ]);

        $this->assertFalse($livro->fresh()->isDisponivel());

        Mail::assertSent(RequisicaoConfirmadaMail::class, 2);
    }

    public function test_nao_e_possivel_requisitar_livro_que_ja_esta_requisitado(): void
    {
        $cidadao1 = $this->criarCidadao('cidadao1@teste.pt');
        $cidadao2 = $this->criarCidadao('cidadao2@teste.pt');
        $livro = $this->criarLivro();

        Requisicao::create([
            'numero_sequencial' => 1,
            'user_id' => $cidadao1->id,
            'livro_id' => $livro->id,
            'foto_cidadao' => $cidadao1->profile_photo_url,
            'data_requisicao' => now()->toDateString(),
            'data_fim_prevista' => now()->addDays(5)->toDateString(),
            'status' => 'ativa',
        ]);

        $response = $this->actingAs($cidadao2)->post(route('requisicoes.store'), [
            'livro_id' => $livro->id,
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(1, Requisicao::count());
    }

    public function test_cidadao_nao_pode_ter_mais_de_tres_livros_em_simultaneo(): void
    {
        $cidadao = $this->criarCidadao();

        $livro1 = $this->criarLivro('Livro 1');
        $livro2 = $this->criarLivro('Livro 2');
        $livro3 = $this->criarLivro('Livro 3');
        $livro4 = $this->criarLivro('Livro 4');

        $this->actingAs($cidadao)->post(route('requisicoes.store'), ['livro_id' => $livro1->id]);
        $this->actingAs($cidadao)->post(route('requisicoes.store'), ['livro_id' => $livro2->id]);
        $this->actingAs($cidadao)->post(route('requisicoes.store'), ['livro_id' => $livro3->id]);

        $this->assertEquals(3, Requisicao::where('user_id', $cidadao->id)->where('status', 'ativa')->count());

        $response = $this->actingAs($cidadao)->post(route('requisicoes.store'), ['livro_id' => $livro4->id]);
        $response->assertSessionHas('error');

        $this->assertEquals(3, Requisicao::count());
    }

    public function test_admin_pode_confirmar_devolucao_e_calcula_dias(): void
    {
        $cidadao = $this->criarCidadao();
        $admin = $this->criarAdmin();
        $livro = $this->criarLivro();

        $requisicao = Requisicao::create([
            'numero_sequencial' => 1,
            'user_id' => $cidadao->id,
            'livro_id' => $livro->id,
            'foto_cidadao' => $cidadao->profile_photo_url,
            'data_requisicao' => now()->subDays(2)->toDateString(),
            'data_fim_prevista' => now()->addDays(3)->toDateString(),
            'status' => 'ativa',
        ]);

        $response = $this->actingAs($admin)->post(route('requisicoes.devolver', $requisicao), [
            'data_devolucao' => now()->toDateString(),
        ]);

        $response->assertSessionHas('status');

        $requisicao->refresh();
        $this->assertEquals('entregue', $requisicao->status);
        $this->assertNotNull($requisicao->data_devolucao);
        $this->assertEquals(2, $requisicao->dias_decorridos);
        $this->assertTrue($livro->fresh()->isDisponivel());
    }

    public function test_cidadao_pode_cancelar_sua_requisicao_ativa(): void
    {
        $cidadao = $this->criarCidadao();
        $livro = $this->criarLivro();

        $requisicao = Requisicao::create([
            'numero_sequencial' => 1,
            'user_id' => $cidadao->id,
            'livro_id' => $livro->id,
            'foto_cidadao' => $cidadao->profile_photo_url,
            'data_requisicao' => now()->toDateString(),
            'data_fim_prevista' => now()->addDays(5)->toDateString(),
            'status' => 'ativa',
        ]);

        $response = $this->actingAs($cidadao)->post(route('requisicoes.cancelar', $requisicao));
        $response->assertSessionHas('status');

        $requisicao->refresh();
        $this->assertEquals('cancelada', $requisicao->status);
        $this->assertTrue($livro->fresh()->isDisponivel());
    }

    public function test_comando_lembretes_envia_email_para_requisicoes_a_terminar_amanha(): void
    {
        Mail::fake();

        $cidadao = $this->criarCidadao();
        $livro = $this->criarLivro();

        Requisicao::create([
            'numero_sequencial' => 1,
            'user_id' => $cidadao->id,
            'livro_id' => $livro->id,
            'foto_cidadao' => $cidadao->profile_photo_url,
            'data_requisicao' => now()->subDays(4)->toDateString(),
            'data_fim_prevista' => now()->addDay()->toDateString(),
            'status' => 'ativa',
        ]);

        $this->artisan('requisicoes:enviar-lembretes')
            ->assertSuccessful();

        Mail::assertSent(LembreteDevolucaoMail::class, 1);
    }
    
    public function test_cidadao_apenas_visualiza_as_suas_proprias_requisicoes(): void
    {
        $cidadao1 = $this->criarCidadao('c1@teste.pt');
        $cidadao2 = $this->criarCidadao('c2@teste.pt');
        $admin = $this->criarAdmin();

        $livro1 = $this->criarLivro('Livro C1');
        $livro2 = $this->criarLivro('Livro C2');

        Requisicao::create([
            'numero_sequencial' => 1,
            'user_id' => $cidadao1->id,
            'livro_id' => $livro1->id,
            'foto_cidadao' => $cidadao1->profile_photo_url,
            'data_requisicao' => now()->toDateString(),
            'data_fim_prevista' => now()->addDays(5)->toDateString(),
            'status' => 'ativa',
        ]);

        Requisicao::create([
            'numero_sequencial' => 2,
            'user_id' => $cidadao2->id,
            'livro_id' => $livro2->id,
            'foto_cidadao' => $cidadao2->profile_photo_url,
            'data_requisicao' => now()->toDateString(),
            'data_fim_prevista' => now()->addDays(5)->toDateString(),
            'status' => 'ativa',
        ]);

        $responseC1 = $this->actingAs($cidadao1)->get(route('requisicoes.index'));
        $responseC1->assertOk();
        $responseC1->assertSee('Livro C1');
        $responseC1->assertDontSee('Livro C2');

        $this->flushSession();

        $responseAdmin = $this->actingAs($admin)->get(route('requisicoes.index'));
        $responseAdmin->assertOk();
        $responseAdmin->assertSee('Livro C1');
        $responseAdmin->assertSee('Livro C2');
    }

    public function test_validacao_rejeita_devolucoes_com_data_anterior_ou_futura(): void
    {
        $cidadao = $this->criarCidadao();
        $admin = $this->criarAdmin();
        $livro = $this->criarLivro();

        $requisicao = Requisicao::create([
            'numero_sequencial' => 1,
            'user_id' => $cidadao->id,
            'livro_id' => $livro->id,
            'foto_cidadao' => $cidadao->profile_photo_url,
            'data_requisicao' => now()->toDateString(),
            'data_fim_prevista' => now()->addDays(5)->toDateString(),
            'status' => 'ativa',
        ]);

        $responseAnterior = $this->actingAs($admin)->post(route('requisicoes.devolver', $requisicao), [
            'data_devolucao' => now()->subDay()->toDateString(),
        ]);
        $responseAnterior->assertSessionHasErrors('data_devolucao');

        $this->flushSession();

        $responseFutura = $this->actingAs($admin)->post(route('requisicoes.devolver', $requisicao), [
            'data_devolucao' => now()->addDays(2)->toDateString(),
        ]);
        $responseFutura->assertSessionHasErrors('data_devolucao');
    }

    public function test_colisao_no_numero_sequencial_recupera_com_sucesso(): void
    {
        Mail::fake();

        $cidadao = $this->criarCidadao();
        $this->criarAdmin();
        $livro1 = $this->criarLivro('Livro 1');
        $livro2 = $this->criarLivro('Livro 2');

        $tentativas = 0;
        $concorrenteGravado = false;

        Event::listen(TransactionRolledBack::class, function () use (&$concorrenteGravado, $cidadao, $livro2) {
            if (!$concorrenteGravado) {
                $concorrenteGravado = true;
                Requisicao::withoutEvents(function () use ($cidadao, $livro2) {
                    Requisicao::create([
                        'numero_sequencial' => 1,
                        'user_id' => $cidadao->id,
                        'livro_id' => $livro2->id,
                        'foto_cidadao' => $cidadao->profile_photo_url,
                        'data_requisicao' => now()->toDateString(),
                        'data_fim_prevista' => now()->addDays(5)->toDateString(),
                        'status' => 'ativa',
                    ]);
                });
            }
        });

        Requisicao::creating(function ($req) use (&$tentativas) {
            $tentativas++;
            if ($tentativas === 1) {
                throw new UniqueConstraintViolationException(
                    'connection',
                    'INSERT INTO requisicoes...',
                    [],
                    new \Exception('Colisão única simulada')
                );
            }
        });

        $response = $this->actingAs($cidadao)->post(route('requisicoes.store'), [
            'livro_id' => $livro1->id,
        ]);

        $response->assertSessionHas('status');

        $this->assertEquals(2, $tentativas);
        $this->assertEquals(2, Requisicao::count());
        $this->assertDatabaseHas('requisicoes', [
            'livro_id' => $livro2->id,
            'numero_sequencial' => 1,
        ]);
        $this->assertDatabaseHas('requisicoes', [
            'livro_id' => $livro1->id,
            'numero_sequencial' => 2,
        ]);
    }
}