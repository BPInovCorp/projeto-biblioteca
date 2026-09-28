<?php

namespace Database\Seeders;

use App\Models\Autor;
use App\Models\Editora;
use App\Models\Livro;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BibliotecaSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('autor_livro')->truncate();
        Livro::truncate();
        Autor::truncate();
        Editora::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $portoEditora = Editora::create([
            'nome' => 'Porto Editora',
            'logotipo' => 'images/editoras/PortoEditora.webp',
        ]);

        $bertrand = Editora::create([
            'nome' => 'Bertrand Editora',
            'logotipo' => 'images/editoras/BertrandEditora.webp',
        ]);

        $caminho = Editora::create([
            'nome' => 'Editorial Caminho',
            'logotipo' => 'images/editoras/EditorialCaminho.webp',
        ]);

        $leya = Editora::create([
            'nome' => 'Leya / Dom Quixote',
            'logotipo' => 'images/editoras/LeyaDomQuixote.webp',
        ]);

        $livrosDoBrasil = Editora::create([
            'nome' => 'Livros do Brasil',
            'logotipo' => 'images/editoras/LivrosDoBrasil.webp',
        ]);

        $planeta = Editora::create([
            'nome' => 'Planeta',
            'logotipo' => 'images/editoras/Planeta.webp',
        ]);

        $almedina = Editora::create([
            'nome' => 'Almedina',
            'logotipo' => 'images/editoras/Almedina.webp',
        ]);

        $penguin = Editora::create([
            'nome' => 'Penguin Clássicos',
            'logotipo' => 'images/editoras/PenguinClassicos.webp',
        ]);

        $cervantes = Autor::create([
            'nome' => 'Miguel de Cervantes',
            'foto' => 'images/autores/MiguelDeCervantes.webp',
        ]);

        $camoes = Autor::create([
            'nome' => 'Luís Vaz de Camões',
            'foto' => 'images/autores/LuisVazDeCamoes.webp',
        ]);

        $saramago = Autor::create([
            'nome' => 'José Saramago',
            'foto' => 'images/autores/JoseSaramago.webp',
        ]);

        $pessoa = Autor::create([
            'nome' => 'Fernando Pessoa',
            'foto' => 'images/autores/FernandoPessoa.webp',
        ]);

        $queiros = Autor::create([
            'nome' => 'Eça de Queirós',
            'foto' => 'images/autores/EcaDeQueiros.webp',
        ]);

        $vicente = Autor::create([
            'nome' => 'Gil Vicente',
            'foto' => 'images/autores/GilVicente.webp',
        ]);

        $tolkien = Autor::create([
            'nome' => 'J.R.R. Tolkien',
            'foto' => 'images/autores/JRRTolkien.webp',
        ]);

        $garrett = Autor::create([
            'nome' => 'Almeida Garrett',
            'foto' => 'images/autores/AlmeidaGarrett.webp',
        ]);

        $livro1 = Livro::create([
            'editora_id' => $portoEditora->id,
            'isbn' => '978-972-0-04001-0',
            'nome' => 'Dom Quixote',
            'bibliografia' => 'A história do fidalgo D. Quixote de La Mancha e do seu fiel escudeiro Sancho Pança que percorrem o mundo em busca de aventuras cavalheirescas, combatendo moinhos de vento e defendendo a justiça.',
            'imagem_capa' => 'images/livros/DomQuixote.webp',
            'preco' => 19.90,
        ]);
        $livro1->autores()->attach($cervantes->id);

        $livro2 = Livro::create([
            'editora_id' => $portoEditora->id,
            'isbn' => '978-972-25-1892-4',
            'nome' => 'Os Lusíadas',
            'bibliografia' => 'Obra épica da literatura portuguesa que narra as viagens marítimas dos navegadores portugueses no século XVI, com particular foco na descoberta do caminho marítimo para a Índia por Vasco da Gama.',
            'imagem_capa' => 'images/livros/OsLusiadas.webp',
            'preco' => 15.50,
        ]);
        $livro2->autores()->attach($camoes->id);

        $livro3 = Livro::create([
            'editora_id' => $caminho->id,
            'isbn' => '978-972-21-0028-1',
            'nome' => 'Memorial do Convento',
            'bibliografia' => 'Romance histórico e fantástico que entrelaça a colossal construção do Convento de Mafra no século XVIII com a paixão entre Baltasar Sete-Sóis e Blimunda Sete-Luas.',
            'imagem_capa' => 'images/livros/MemorialDoConvento.webp',
            'preco' => 18.20,
        ]);
        $livro3->autores()->attach($saramago->id);

        $livro4 = Livro::create([
            'editora_id' => $portoEditora->id,
            'isbn' => '978-972-20-4105-7',
            'nome' => 'Mensagem',
            'bibliografia' => 'A única obra poética em língua portuguesa publicada em vida por Fernando Pessoa. Uma viagem mística, histórica e simbólica sobre a grandeza e o destino de Portugal.',
            'imagem_capa' => 'images/livros/Mensagem.webp',
            'preco' => 12.00,
        ]);
        $livro4->autores()->attach($pessoa->id);

        $livro5 = Livro::create([
            'editora_id' => $portoEditora->id,
            'isbn' => '978-972-0-04005-8',
            'nome' => 'O Primo Basílio',
            'bibliografia' => 'Obra-prima do realismo português centrada no drama conjugal de Jorge e Luísa no seio da sociedade lisboeta do século XIX, perante as convenções sociais e a chantagem de Juliana.',
            'imagem_capa' => 'images/livros/OPrimoBasilio.webp',
            'preco' => 16.90,
        ]);
        $livro5->autores()->attach($queiros->id);

        $livro6 = Livro::create([
            'editora_id' => $livrosDoBrasil->id,
            'isbn' => '978-972-25-2010-1',
            'nome' => 'A Cidade e as Serras',
            'bibliografia' => 'Contraste vívido entre a vida artificial e hiper-tecnológica de Paris e o regresso revigorante e autêntico à natureza e às origens nas serras de Tormes, em Portugal.',
            'imagem_capa' => 'images/livros/ACidadeEAsSerras.webp',
            'preco' => 14.50,
        ]);
        $livro6->autores()->attach($queiros->id);

        $livro7 = Livro::create([
            'editora_id' => $portoEditora->id,
            'isbn' => '978-972-21-0500-2',
            'nome' => 'Auto da Barca do Inferno',
            'bibliografia' => 'Peça satírica clássica do teatro vicentino onde diversas personagens que representam os estratos sociais do século XVI são julgadas no cais entre a Barca da Glória e a Barca do Inferno.',
            'imagem_capa' => 'images/livros/AutoDaBarcaDoInferno.webp',
            'preco' => 9.90,
        ]);
        $livro7->autores()->attach($vicente->id);

        $livro8 = Livro::create([
            'editora_id' => $caminho->id,
            'isbn' => '978-972-21-1002-0',
            'nome' => 'Ensaio sobre a Cegueira',
            'bibliografia' => 'Uma alegoria profunda sobre a fragilidade humana e social perante uma epidemia de cegueira branca que assola uma cidade, desnudando os mais primitivos instintos do ser humano.',
            'imagem_capa' => 'images/livros/EnsaioSobreACegueira.webp',
            'preco' => 21.00,
        ]);
        $livro8->autores()->attach($saramago->id);

        $livro9 = Livro::create([
            'editora_id' => $portoEditora->id,
            'isbn' => '978-972-0-04010-2',
            'nome' => 'Os Maias',
            'bibliografia' => 'Crónica da decadência aristocrática da sociedade lisboeta através da tragédia amorosa entre Carlos da Maia e Maria Eduarda.',
            'imagem_capa' => 'images/livros/OsMaias.webp',
            'preco' => 22.50,
        ]);
        $livro9->autores()->attach($queiros->id);

        $livro10 = Livro::create([
            'editora_id' => $planeta->id,
            'isbn' => '978-972-20-4750-9',
            'nome' => 'O Hobbit',
            'bibliografia' => 'A inesquecível aventura de Bilbo Baggins através da Terra Média com Gandalf e treze anões para recuperar o tesouro guardado pelo dragão Smaug na Montanha Solitária.',
            'imagem_capa' => 'images/livros/OHobbit.webp',
            'preco' => 18.50,
        ]);
        $livro10->autores()->attach($tolkien->id);

        $livro11 = Livro::create([
            'editora_id' => $portoEditora->id,
            'isbn' => '978-972-0-04020-1',
            'nome' => 'Livro do Desassossego',
            'bibliografia' => 'Diário íntimo de reflexões existenciais, fragmentárias e poéticas de Bernardo Soares, semi-heterónimo de Fernando Pessoa, num retrato profundo da alma humana.',
            'imagem_capa' => 'images/livros/LivroDoDesassossego.webp',
            'preco' => 24.00,
        ]);
        $livro11->autores()->attach($pessoa->id);

        $livro12 = Livro::create([
            'editora_id' => $caminho->id,
            'isbn' => '978-972-21-0850-8',
            'nome' => 'O Ano da Morte de Ricardo Reis',
            'bibliografia' => 'No ano tumultuoso de 1936, o heterónimo Ricardo Reis regressa a Lisboa após a morte de Fernando Pessoa, vagueando por um país à beira do autoritarismo.',
            'imagem_capa' => 'images/livros/OAnoDaMorteDeRicardoReis.webp',
            'preco' => 19.50,
        ]);
        $livro12->autores()->attach($saramago->id);

        $livro13 = Livro::create([
            'editora_id' => $almedina->id,
            'isbn' => '978-972-20-4500-0',
            'nome' => 'O Banqueiro Anarquista',
            'bibliografia' => 'Um provocador conto satírico em que um abastado banqueiro demonstra através de rigorosa lógica dedutiva como a sua acumulação de capital é a única forma genuína de anarquismo.',
            'imagem_capa' => 'images/livros/OBanqueiroAnarquista.webp',
            'preco' => 11.50,
        ]);
        $livro13->autores()->attach($pessoa->id);

        $livro14 = Livro::create([
            'editora_id' => $portoEditora->id,
            'isbn' => '978-972-25-2200-6',
            'nome' => 'Viagens na Minha Terra',
            'bibliografia' => 'Narrativa precursora da prosa moderna em Portugal, cruzando o relato de viagem entre Lisboa e Santarém com reflexões filosóficas, políticas e um drama romântico.',
            'imagem_capa' => 'images/livros/ViagensNaMinhaTerra.webp',
            'preco' => 14.00,
        ]);
        $livro14->autores()->attach($garrett->id);

        $livro15 = Livro::create([
            'editora_id' => $livrosDoBrasil->id,
            'isbn' => '978-972-20-4600-7',
            'nome' => 'A Ilustre Casa de Ramires',
            'bibliografia' => 'Gonçalo Mendes Ramires, herdeiro de uma das mais antigas linhagens nobres de Portugal, procura afirmação política e literária enquanto escreve a novela histórica dos seus antepassados medievais.',
            'imagem_capa' => 'images/livros/AIlustreCasaDeRamires.webp',
            'preco' => 16.00,
        ]);
        $livro15->autores()->attach($queiros->id);

        $livro16 = Livro::create([
            'editora_id' => $portoEditora->id,
            'isbn' => '978-972-21-0600-9',
            'nome' => 'Farsa de Inês Pereira',
            'bibliografia' => 'Comédia de costumes em que Inês Pereira desdenha um pretendente sensato em busca de um marido cortês e refinado, aprendendo com astúcia a ditar as suas próprias regras.',
            'imagem_capa' => 'images/livros/FarsaDeInesPereira.webp',
            'preco' => 10.50,
        ]);
        $livro16->autores()->attach($vicente->id);

        $livro17 = Livro::create([
            'editora_id' => $portoEditora->id,
            'isbn' => '978-972-0-04030-0',
            'nome' => 'A Relíquia',
            'bibliografia' => 'Sátira hilariante sobre a hipocrisia religiosa, em que Teodorico Raposo viaja à Terra Santa para obter uma relíquia milagrosa e assegurar a herança da sua beata tia D. Patrocínio.',
            'imagem_capa' => 'images/livros/AReliquia.webp',
            'preco' => 15.00,
        ]);
        $livro17->autores()->attach($queiros->id);

        $livro18 = Livro::create([
            'editora_id' => $penguin->id,
            'isbn' => '978-972-25-2300-3',
            'nome' => 'Frei Luís de Sousa',
            'bibliografia' => 'Tragédia romântica exemplar do teatro português sobre honra, fé e fatalidade familiar decorrente do regresso inesperado de D. João de Portugal após a batalha de Alcácer Quibir.',
            'imagem_capa' => 'images/livros/FreiLuisDeSousa.webp',
            'preco' => 12.50,
        ]);
        $livro18->autores()->attach($garrett->id);
    }
}