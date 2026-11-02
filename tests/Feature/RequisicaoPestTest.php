<?php

use App\Models\Editora;
use App\Models\Livro;
use App\Models\Requisicao;
use App\Models\User;

function criarLivroParaPest(string $nome = 'Mensagem', string $preco = '15.00'): Livro
{
    $editora = Editora::create([
        'nome' => 'Editora Lusitana',
        'morada' => 'Rua Central, 10',
        'telefone' => '912345678',
        'nif' => '123456789',
        'email' => 'editora@lusitana.pt',
    ]);

    return Livro::create([
        'editora_id' => $editora->id,
        'isbn' => '9789720049774',
        'nome' => $nome,
        'bibliografia' => 'Obra poética de Fernando Pessoa.',
        'preco' => $preco,
    ]);
}

test('utilizador pode criar uma requisicao de livro corretamente', function () {
    $cidadao = User::factory()->create([
        'name' => 'João Leitor',
        'email' => 'joao.leitor@exemplo.pt',
        'role' => 'cidadao',
    ]);

    $livro = criarLivroParaPest('Mensagem');

    $resposta = $this->actingAs($cidadao)->post(route('requisicoes.store'), [
        'livro_id' => $livro->id,
    ]);

    $resposta->assertRedirect();
    $resposta->assertSessionHas('status');

    $this->assertDatabaseHas('requisicoes', [
        'user_id' => $cidadao->id,
        'livro_id' => $livro->id,
        'status' => 'ativa',
        'numero_sequencial' => 1,
    ]);

    $requisicao = Requisicao::where('user_id', $cidadao->id)->first();
    expect($requisicao)->not->toBeNull()
        ->and($requisicao->livro_id)->toBe($livro->id)
        ->and($requisicao->status)->toBe('ativa')
        ->and($requisicao->numero_sequencial)->toBe(1)
        ->and($requisicao->data_requisicao->toDateString())->toBe(now()->toDateString())
        ->and($requisicao->data_fim_prevista->toDateString())->toBe(now()->addDays(5)->toDateString());
});

test('requisicao nao pode ser criada sem um livro valido', function () {
    $cidadao = User::factory()->create([
        'role' => 'cidadao',
    ]);

    $respostaSemLivro = $this->actingAs($cidadao)->post(route('requisicoes.store'), [
        'livro_id' => null,
    ]);

    $respostaSemLivro->assertSessionHasErrors(['livro_id']);

    $respostaLivroInexistente = $this->actingAs($cidadao)->post(route('requisicoes.store'), [
        'livro_id' => 99999,
    ]);

    $respostaLivroInexistente->assertSessionHasErrors(['livro_id']);

    $this->assertDatabaseCount('requisicoes', 0);
    expect(Requisicao::count())->toBe(0);
});

test('administrador pode confirmar a devolucao de um livro', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $cidadao = User::factory()->create([
        'role' => 'cidadao',
    ]);

    $livro = criarLivroParaPest('Amor de Perdição');

    $requisicao = Requisicao::create([
        'numero_sequencial' => 1,
        'user_id' => $cidadao->id,
        'livro_id' => $livro->id,
        'foto_cidadao' => $cidadao->profile_photo_url,
        'data_requisicao' => now()->subDays(2)->toDateString(),
        'data_fim_prevista' => now()->addDays(3)->toDateString(),
        'status' => 'ativa',
    ]);

    $resposta = $this->actingAs($admin)->post(route('requisicoes.devolver', $requisicao), [
        'data_devolucao' => now()->toDateString(),
    ]);

    $resposta->assertRedirect();
    $resposta->assertSessionHas('status');

    $requisicao->refresh();

    expect($requisicao->status)->toBe('entregue')
        ->and($requisicao->data_devolucao->toDateString())->toBe(now()->toDateString())
        ->and($requisicao->dias_decorridos)->toBe(2);

    $this->assertDatabaseHas('requisicoes', [
        'id' => $requisicao->id,
        'status' => 'entregue',
    ]);
});

test('utilizador apenas visualiza as suas proprias requisicoes com isolamento', function () {
    $cidadaoA = User::factory()->create([
        'name' => 'Cidadão Alfa',
        'email' => 'alfa@exemplo.pt',
        'role' => 'cidadao',
    ]);

    $cidadaoB = User::factory()->create([
        'name' => 'Cidadão Beta',
        'email' => 'beta@exemplo.pt',
        'role' => 'cidadao',
    ]);

    $livroA = criarLivroParaPest('Livro do Alfa');
    $livroB = criarLivroParaPest('Livro do Beta');

    $requisicaoA = Requisicao::create([
        'numero_sequencial' => 10,
        'user_id' => $cidadaoA->id,
        'livro_id' => $livroA->id,
        'foto_cidadao' => $cidadaoA->profile_photo_url,
        'data_requisicao' => now()->toDateString(),
        'data_fim_prevista' => now()->addDays(5)->toDateString(),
        'status' => 'ativa',
    ]);

    $requisicaoB = Requisicao::create([
        'numero_sequencial' => 20,
        'user_id' => $cidadaoB->id,
        'livro_id' => $livroB->id,
        'foto_cidadao' => $cidadaoB->profile_photo_url,
        'data_requisicao' => now()->toDateString(),
        'data_fim_prevista' => now()->addDays(5)->toDateString(),
        'status' => 'ativa',
    ]);

    $respostaAlfa = $this->actingAs($cidadaoA)->get(route('requisicoes.index'));

    $respostaAlfa->assertOk();
    $respostaAlfa->assertSee('Livro do Alfa');
    $respostaAlfa->assertDontSee('Livro do Beta');
    $respostaAlfa->assertSee('#0010');
    $respostaAlfa->assertDontSee('#0020');

    expect($cidadaoA->requisicoes()->count())->toBe(1)
        ->and($cidadaoB->requisicoes()->count())->toBe(1);
});

test('nao e possivel requisitar livro sem stock disponivel', function () {
    $cidadao1 = User::factory()->create(['role' => 'cidadao']);
    $cidadao2 = User::factory()->create(['role' => 'cidadao']);

    $livro = criarLivroParaPest('Memorial do Convento');

    Requisicao::create([
        'numero_sequencial' => 1,
        'user_id' => $cidadao1->id,
        'livro_id' => $livro->id,
        'foto_cidadao' => $cidadao1->profile_photo_url,
        'data_requisicao' => now()->toDateString(),
        'data_fim_prevista' => now()->addDays(5)->toDateString(),
        'status' => 'ativa',
    ]);

    expect($livro->fresh()->isDisponivel())->toBeFalse();

    $respostaTentativa = $this->actingAs($cidadao2)->post(route('requisicoes.store'), [
        'livro_id' => $livro->id,
    ]);

    $respostaTentativa->assertRedirect();
    $respostaTentativa->assertSessionHas('error', __('Livro indisponível para requisição.'));

    $this->assertDatabaseCount('requisicoes', 1);
    expect(Requisicao::where('user_id', $cidadao2->id)->count())->toBe(0);
});