<?php

use App\Models\AuditoriaLog;
use App\Models\Autor;
use App\Models\Editora;
use App\Models\User;
use Illuminate\Support\Carbon;

test('apenas administradores podem aceder a listagem de logs', function () {
    $this->get(route('admin.logs.index'))
        ->assertRedirect(route('login'));

    $cidadao = User::factory()->create(['role' => 'cidadao']);

    $this->actingAs($cidadao)
        ->get(route('admin.logs.index'))
        ->assertStatus(403);

    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.logs.index'))
        ->assertOk()
        ->assertSee('Logs de Auditoria');
});

test('acoes criticas no catalogo geram logs de auditoria automaticamente com ip e browser', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $editora = Editora::create([
        'nome' => 'Editora Pest Audit',
        'morada' => 'Rua do Audit',
        'telefone' => '912345678',
        'nif' => '123456789',
        'email' => 'audit@editora.pt',
    ]);

    $autor = Autor::create([
        'nome' => 'Autor Auditado',
        'biografia' => 'Biografia do autor.',
    ]);

    $this->actingAs($admin)
        ->withServerVariables([
            'REMOTE_ADDR' => '192.168.1.100',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.0.0',
        ])
        ->post(route('livros.store'), [
            'isbn' => '9789720049999',
            'nome' => 'Livro Auditado via PEST',
            'editora_id' => $editora->id,
            'autores' => [$autor->id],
            'preco' => '25.00',
        ]);

    $log = AuditoriaLog::where('modulo', 'Livros')->latest('id')->first();

    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBe($admin->id)
        ->and($log->alteracao)->toContain('Criou o livro')
        ->and($log->alteracao)->toContain('Livro Auditado via PEST')
        ->and($log->ip)->toBe('192.168.1.100')
        ->and($log->browser)->toBe('Microsoft Edge');
});

test('filtros por modulo e intervalo de datas funcionam corretamente em pest', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    AuditoriaLog::forceCreate([
        'user_id' => $admin->id,
        'modulo' => 'Requisições',
        'objeto_id' => '1',
        'alteracao' => 'Ação de Requisição Dentro do Prazo',
        'ip' => '127.0.0.1',
        'browser' => 'Google Chrome',
        'created_at' => Carbon::create(2026, 10, 15, 12, 0, 0),
    ]);

    AuditoriaLog::forceCreate([
        'user_id' => $admin->id,
        'modulo' => 'Encomendas',
        'objeto_id' => '2',
        'alteracao' => 'Ação de Encomenda Fora do Filtro',
        'ip' => '127.0.0.1',
        'browser' => 'Google Chrome',
        'created_at' => Carbon::create(2026, 10, 15, 14, 0, 0),
    ]);

    AuditoriaLog::forceCreate([
        'user_id' => $admin->id,
        'modulo' => 'Requisições',
        'objeto_id' => '3',
        'alteracao' => 'Ação de Requisição Fora da Data',
        'ip' => '127.0.0.1',
        'browser' => 'Google Chrome',
        'created_at' => Carbon::create(2026, 10, 25, 10, 0, 0),
    ]);

    $resposta = $this->actingAs($admin)->get(route('admin.logs.index', [
        'modulo' => 'Requisições',
        'data_inicio' => '2026-10-10',
        'data_fim' => '2026-10-20',
    ]));

    $resposta->assertOk();
    $resposta->assertSee('Ação de Requisição Dentro do Prazo');
    $resposta->assertDontSee('Ação de Encomenda Fora do Filtro');
    $resposta->assertDontSee('Ação de Requisição Fora da Data');
});
test('login, logout e registo de utilizadores geram logs de auditoria', function () {
    $this->post('/register', [
        'name' => 'Novo Leitor',
        'email' => 'novo@example.com',
        'password' => 'password-segura-123',
        'password_confirmation' => 'password-segura-123',
        'terms' => true,
    ]);

    $novo = App\Models\User::where('email', 'novo@example.com')->firstOrFail();
    $this->post('/logout');
    $this->post('/login', ['email' => 'novo@example.com', 'password' => 'password-segura-123']);
    $this->post('/logout');
    $this->post('/login', ['email' => 'novo@example.com', 'password' => 'errada']);

    $alteracoes = App\Models\AuditoriaLog::where('modulo', 'Autentica��o')->pluck('alteracao')->implode('|');

    expect($alteracoes)->toContain('Registou uma nova conta')
        ->toContain('Iniciou sess�o')
        ->toContain('Terminou sess�o')
        ->toContain('Tentativa de login falhada');
    expect(App\Models\AuditoriaLog::where('modulo', 'Autentica��o')->whereNotNull('ip')->count())->toBeGreaterThan(0);
});
