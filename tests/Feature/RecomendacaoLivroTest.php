<?php

namespace Tests\Feature;

use App\Models\Autor;
use App\Models\Editora;
use App\Models\Livro;
use App\Services\RecomendacaoLivroService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecomendacaoLivroTest extends TestCase
{
    use RefreshDatabase;

    private function criarLivroComDetalhes(string $nome, string $isbn, ?string $sinopse = null, ?Autor $autor = null): Livro
    {
        $editora = Editora::firstOrCreate(['nome' => 'Editora Teste']);

        $livro = Livro::create([
            'editora_id' => $editora->id,
            'isbn' => $isbn,
            'nome' => $nome,
            'bibliografia' => $sinopse,
            'preco' => '15.00',
        ]);

        if ($autor) {
            $livro->autores()->attach($autor->id);
        }

        return $livro;
    }

    public function test_algoritmo_recomenda_livros_com_palavras_chave_comuns_na_sinopse(): void
    {
        $servico = app(RecomendacaoLivroService::class);

        $livroPrincipal = $this->criarLivroComDetalhes(
            'Os Lusíadas',
            '9789890000001',
            'Viagens marítimas e navegadores portugueses pelo oceano à conquista da Índia.'
        );

        $livroAfim = $this->criarLivroComDetalhes(
            'Viagens na Minha Terra',
            '9789890000002',
            'Narrativa sobre navegadores e viagens pelo oceano com exploração marítima.'
        );

        $livroDistante = $this->criarLivroComDetalhes(
            'O Banqueiro Anarquista',
            '9789890000003',
            'Tratado satírico sobre finanças, economia, capital e bancos em Lisboa.'
        );

        $recomendados = $servico->recomendar($livroPrincipal, 3);

        $this->assertNotEmpty($recomendados);
        $this->assertEquals($livroAfim->id, $recomendados[0]['id']);
        $this->assertGreaterThan(0, $recomendados[0]['pontuacao']);
    }

    public function test_livro_consultado_e_obrigatoriamente_excluido_das_suas_proprias_recomendacoes(): void
    {
        $servico = app(RecomendacaoLivroService::class);

        $livro = $this->criarLivroComDetalhes(
            'Memorial do Convento',
            '9789890000004',
            'Construção do Convento de Mafra no século XVIII com Baltasar e Blimunda.'
        );

        $this->criarLivroComDetalhes(
            'Outro Livro de Mafra',
            '9789890000005',
            'História sobre o Convento de Mafra e a construção monumental.'
        );

        $recomendados = $servico->recomendar($livro, 3);

        $idsRecomendados = array_column($recomendados, 'id');
        $this->assertNotContains($livro->id, $idsRecomendados);
    }

    public function test_algoritmo_aplica_bonus_de_afinidade_para_obras_do_mesmo_autor(): void
    {
        $servico = app(RecomendacaoLivroService::class);

        $saramago = Autor::create(['nome' => 'José Saramago']);
        $outroAutor = Autor::create(['nome' => 'Outro Escritor']);

        $livroBase = $this->criarLivroComDetalhes(
            'Ensaio sobre a Cegueira',
            '9789890000006',
            'Epidemia inexplicável que atinge a sociedade e desnuda a fragilidade humana.',
            $saramago
        );

        $livroMesmoAutor = $this->criarLivroComDetalhes(
            'Ensaio sobre a Lucidez',
            '9789890000007',
            'Reflexão política sobre eleições onde a sociedade decide votar em branco.',
            $saramago
        );

        $livroOutroAutor = $this->criarLivroComDetalhes(
            'Epidemia Urbana',
            '9789890000008',
            'Uma epidemia biológica contagiosa que atinge a sociedade moderna.',
            $outroAutor
        );

        $recomendados = $servico->recomendar($livroBase, 3);

        $this->assertNotEmpty($recomendados);
        $ids = array_column($recomendados, 'id');
        $this->assertContains($livroMesmoAutor->id, $ids);
    }

    public function test_comportamento_gracioso_quando_obras_nao_tem_sinopse_ou_palavras_comuns(): void
    {
        $servico = app(RecomendacaoLivroService::class);

        $livroSemSinopse = $this->criarLivroComDetalhes('Alfa', '9789890000009', null);
        $this->criarLivroComDetalhes('Beta', '9789890000010', null);

        $recomendados = $servico->recomendar($livroSemSinopse, 3);

        $this->assertIsArray($recomendados);
        $this->assertEmpty($recomendados);
    }

    public function test_respeita_o_limite_maximo_de_recomendacoes(): void
    {
        $servico = app(RecomendacaoLivroService::class);

        $livroCentral = $this->criarLivroComDetalhes('Central', '9789890000011', 'Aventura espacial galáxia planeta estrelas.');

        for ($i = 1; $i <= 5; $i++) {
            $this->criarLivroComDetalhes("Espacial {$i}", "978989000001{$i}", 'Aventura na galáxia espacial com estrelas e planeta.');
        }

        $recomendados = $servico->recomendar($livroCentral, 3);

        $this->assertCount(3, $recomendados);
    }

    public function test_normalizacao_de_acentos_e_maiusculas_na_analise_textual(): void
    {
        $servico = app(RecomendacaoLivroService::class);

        $termos1 = $servico->extrairPalavrasChave('NAVEGAÇÃO marítima dos OCEANOS');
        $termos2 = $servico->extrairPalavrasChave('navegacao maritima dos oceanos');

        sort($termos1);
        sort($termos2);

        $this->assertEquals($termos1, $termos2);
        $this->assertContains('navegacao', $termos1);
        $this->assertContains('maritima', $termos1);
        $this->assertContains('oceanos', $termos1);
        $this->assertNotContains('dos', $termos1);
    }
}