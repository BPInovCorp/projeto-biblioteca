<?php

namespace Tests\Feature;

use App\Models\Autor;
use App\Models\Editora;
use App\Models\Livro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleBooksImportTest extends TestCase
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

    private function mockGoogleBooksResponse(): void
    {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'totalItems' => 1,
                'items' => [
                    [
                        'id' => 'mock_google_id_123',
                        'volumeInfo' => [
                            'title' => 'Livro Mock Google',
                            'authors' => ['Autor Mockado'],
                            'publisher' => 'Editora Mockada',
                            'publishedDate' => '2026',
                            'description' => 'Sinopse simulada para testes.',
                            'industryIdentifiers' => [
                                ['type' => 'ISBN_13', 'identifier' => '9789890000001']
                            ],
                            'imageLinks' => [
                                'thumbnail' => 'https://books.google.com/mock.jpg'
                            ]
                        ],
                        'saleInfo' => [
                            'retailPrice' => [
                                'amount' => 18.50,
                                'currencyCode' => 'EUR'
                            ]
                        ]
                    ]
                ]
            ], 200),
        ]);
    }

    public function test_apenas_administrador_pode_aceder_as_rotas_de_importacao_e_sincronizacao(): void
    {
        $editora = Editora::create(['nome' => 'Editora Base']);
        $livro = Livro::create([
            'editora_id' => $editora->id,
            'isbn' => '9789890000002',
            'nome' => 'Livro Base',
            'preco' => '10.00',
        ]);

        $this->get(route('livros.importar'))->assertRedirect('/login');
        $this->get(route('livros.importar.search', ['query' => 'teste']))->assertRedirect('/login');
        $this->post(route('livros.importar.store'), ['nome' => 'Livro'])->assertRedirect('/login');
        $this->post(route('livros.sincronizar', $livro))->assertRedirect('/login');

        $this->flushSession();
        $cidadao = $this->criarCidadao();

        $this->actingAs($cidadao)->get(route('livros.importar'))->assertStatus(403);
        $this->actingAs($cidadao)->get(route('livros.importar.search', ['query' => 'teste']))->assertStatus(403);
        $this->actingAs($cidadao)->post(route('livros.importar.store'), ['nome' => 'Livro'])->assertStatus(403);
        $this->actingAs($cidadao)->post(route('livros.sincronizar', $livro))->assertStatus(403);

        $this->flushSession();
        $admin = $this->criarAdmin();

        $this->actingAs($admin)->get(route('livros.importar'))->assertOk();
    }

    public function test_pesquisa_google_books_devolve_resultados_normalizados_com_mock(): void
    {
        $this->mockGoogleBooksResponse();

        $admin = $this->criarAdmin();

        $response = $this->actingAs($admin)->get(route('livros.importar.search', [
            'query' => 'Mock',
            'tipo' => 'geral',
        ]));

        $response->assertOk();
        $response->assertSee('Livro Mock Google');
        $response->assertSee('Autor Mockado');
        $response->assertSee('Editora Mockada');
        $response->assertSee('9789890000001');
    }

    public function test_importacao_cria_livro_editora_e_autores_com_sucesso(): void
    {
        $admin = $this->criarAdmin();

        $response = $this->actingAs($admin)->post(route('livros.importar.store'), [
            'nome' => 'Livro Importado Teste',
            'editora' => 'Nova Editora Importada',
            'autores' => ['Autor 1', 'Autor 2'],
            'isbn' => '9789891112223',
            'bibliografia' => 'Sinopse importada de teste.',
            'imagem_capa' => 'https://books.google.com/capa_teste.jpg',
            'preco' => 19.99,
        ]);

        $response->assertRedirect(route('livros.index'));
        $response->assertSessionHas('success');

        $editora = Editora::all()->first(fn($e) => $e->nome === 'Nova Editora Importada');
        $this->assertNotNull($editora);

        $autor1 = Autor::all()->first(fn($a) => $a->nome === 'Autor 1');
        $this->assertNotNull($autor1);

        $autor2 = Autor::all()->first(fn($a) => $a->nome === 'Autor 2');
        $this->assertNotNull($autor2);

        $livro = Livro::all()->first(fn($l) => $l->isbn === '9789891112223');
        $this->assertNotNull($livro);
        $this->assertEquals('Livro Importado Teste', $livro->nome);
        $this->assertEquals('https://books.google.com/capa_teste.jpg', $livro->imagem_capa);
        $this->assertEquals('19.99', (string) $livro->preco);
        $this->assertCount(2, $livro->autores);
    }

    public function test_importacao_reutiliza_autor_existente_por_desduplicacao_canonica(): void
    {
        $admin = $this->criarAdmin();

        $autorExistente = Autor::create([
            'nome' => 'J.R.R. Tolkien',
            'foto' => null,
        ]);

        $this->actingAs($admin)->post(route('livros.importar.store'), [
            'nome' => 'O Silmarillion',
            'editora' => 'Publicações Dom Quixote',
            'autores' => ['J. R. R. Tolkien'],
            'isbn' => '9789722068995',
            'bibliografia' => 'História de Arda.',
            'imagem_capa' => null,
            'preco' => 20.00,
        ]);

        $this->assertEquals(1, Autor::count());

        $livro = Livro::all()->first(fn($l) => $l->isbn === '9789722068995');
        $this->assertNotNull($livro);
        $this->assertEquals($autorExistente->id, $livro->autores->first()->id);
    }

    public function test_importacao_bloqueia_livro_com_isbn_duplicado(): void
    {
        $admin = $this->criarAdmin();
        $editora = Editora::create(['nome' => 'Editora Teste']);

        Livro::create([
            'editora_id' => $editora->id,
            'isbn' => '9789720040010',
            'nome' => 'Livro Original',
            'preco' => '15.00',
        ]);

        $response = $this->actingAs($admin)->post(route('livros.importar.store'), [
            'nome' => 'Tentativa Duplicada',
            'editora' => 'Outra Editora',
            'autores' => ['Outro Autor'],
            'isbn' => '978-972-0-04001-0',
            'preco' => 15.00,
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(1, Livro::count());
    }

    public function test_importacao_atribui_preco_sob_consulta_quando_sem_valor_comercial(): void
    {
        $admin = $this->criarAdmin();

        $this->actingAs($admin)->post(route('livros.importar.store'), [
            'nome' => 'Obra Sem Preço',
            'editora' => 'Editora Gratuita',
            'autores' => ['Autor Qualquer'],
            'isbn' => '9789895556667',
            'preco' => null,
        ]);

        $livro = Livro::all()->first(fn($l) => $l->isbn === '9789895556667');
        $this->assertNotNull($livro);
        $this->assertEquals('Preço sob consulta', $livro->preco);
    }

    public function test_sincronizacao_enriquece_apenas_campos_vazios_sem_destruir_dados_locais(): void
    {
        $this->mockGoogleBooksResponse();

        $admin = $this->criarAdmin();

        $editoraOriginal = Editora::create(['nome' => 'Porto Editora']);
        $autorOriginal = Autor::create(['nome' => 'Luís de Camões']);

        $livro = Livro::create([
            'editora_id' => $editoraOriginal->id,
            'isbn' => '9789890000001',
            'nome' => 'Os Lusíadas Edição Especial',
            'bibliografia' => null,
            'imagem_capa' => 'images/livros/capa_local_intacta.webp',
            'preco' => '25.50',
        ]);
        $livro->autores()->attach($autorOriginal->id);

        $response = $this->actingAs($admin)->post(route('livros.sincronizar', $livro));
        $response->assertSessionHas('success');

        $livro->refresh();

        $this->assertEquals('Sinopse simulada para testes.', $livro->bibliografia);
        $this->assertEquals('images/livros/capa_local_intacta.webp', $livro->imagem_capa);
        $this->assertEquals('25.50', $livro->preco);
        $this->assertEquals($editoraOriginal->id, $livro->editora_id);
        $this->assertEquals($autorOriginal->id, $livro->autores->first()->id);
    }

    public function test_pesquisa_trata_resposta_503_da_google_com_erro_gracioso(): void
    {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'error' => [
                    'code' => 503,
                    'message' => 'Service temporarily unavailable.',
                ],
            ], 503),
        ]);

        $admin = $this->criarAdmin();

        $response = $this->actingAs($admin)->get(route('livros.importar.search', [
            'query' => 'TermoComFalha',
            'tipo' => 'geral',
        ]));

        $response->assertOk();
        $response->assertSee('Instabilidade da Google Books API');
    }

    public function test_importacao_lida_com_obras_sem_autor_sem_editora_e_sem_capa(): void
    {
        $admin = $this->criarAdmin();

        $response = $this->actingAs($admin)->post(route('livros.importar.store'), [
            'nome' => 'Obra Minimalista',
            'editora' => null,
            'autores' => [],
            'isbn' => null,
            'bibliografia' => null,
            'imagem_capa' => null,
            'preco' => 12.00,
        ]);

        $response->assertRedirect(route('livros.index'));
        $response->assertSessionHas('success');

        $editora = Editora::all()->first(fn($e) => $e->nome === 'Editora Desconhecida');
        $this->assertNotNull($editora);

        $autor = Autor::all()->first(fn($a) => $a->nome === 'Autor Desconhecido');
        $this->assertNotNull($autor);

        $livro = Livro::all()->first(fn($l) => $l->nome === 'Obra Minimalista');
        $this->assertNotNull($livro);
        $this->assertNull($livro->imagem_capa);
        $this->assertEquals($editora->id, $livro->editora_id);
        $this->assertEquals($autor->id, $livro->autores->first()->id);
    }
}