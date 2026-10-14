<?php

namespace App\Console\Commands;

use App\Models\Autor;
use App\Models\Editora;
use App\Models\Livro;
use App\Services\GoogleBooksService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class SincronizarLivrosGoogle extends Command
{
    protected $signature = 'livros:sincronizar-google {--limit=10 : Número máximo de livros a processar} {--force : Atualizar campos mesmo que já estejam preenchidos}';

    protected $description = 'Sincroniza e enriquece os metadados dos livros do acervo local a partir da Google Books API.';

    public function handle(GoogleBooksService $googleBooks): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $force = (bool) $this->option('force');

        $this->info("A procurar livros locais para sincronização (Limite: {$limit}, Forçar: " . ($force ? 'Sim' : 'Não') . ")...");

        $todosLivros = Livro::with(['editora', 'autores'])->get();

        if (!$force) {
            $livrosParaProcessar = $todosLivros->filter(function ($livro) {
                return empty($livro->bibliografia)
                    || empty($livro->imagem_capa)
                    || Str::startsWith((string) $livro->isbn, 'SEM-ISBN-')
                    || empty(optional($livro->editora)->nome)
                    || optional($livro->editora)->nome === 'Editora Desconhecida'
                    || $livro->autores->isEmpty();
            })->take($limit);
        } else {
            $livrosParaProcessar = $todosLivros->take($limit);
        }

        if ($livrosParaProcessar->isEmpty()) {
            $this->info('Todos os livros do acervo já possuem metadados completos.');
            return Command::SUCCESS;
        }

        $sincronizados = 0;
        $semCorrespondencia = 0;

        foreach ($livrosParaProcessar as $livro) {
            $itemEncontrado = null;

            $isbnLimpo = preg_replace('/[^0-9xX]/', '', (string) $livro->isbn);
            if (!empty($isbnLimpo) && !Str::startsWith((string) $livro->isbn, 'SEM-ISBN-')) {
                $busca = $googleBooks->search($isbnLimpo, 'isbn', 5);
                if (!empty($busca['items'])) {
                    $itemEncontrado = $busca['items'][0];
                }
            }

            if (!$itemEncontrado && !empty($livro->nome)) {
                $busca = $googleBooks->search($livro->nome, 'titulo', 5);
                if (!empty($busca['items'])) {
                    $itemEncontrado = $busca['items'][0];
                }
            }

            if (!$itemEncontrado && !empty($livro->nome)) {
                $busca = $googleBooks->search($livro->nome, 'geral', 5);
                if (!empty($busca['items'])) {
                    $itemEncontrado = $busca['items'][0];
                }
            }

            if (!$itemEncontrado) {
                $semCorrespondencia++;
                $this->line("✖ [Sem correspondência] {$livro->nome}");
                continue;
            }

            try {
                DB::transaction(function () use ($livro, $itemEncontrado) {
                    if (empty($livro->bibliografia) && !empty($itemEncontrado['bibliografia'])) {
                        $livro->bibliografia = $itemEncontrado['bibliografia'];
                    }

                    if (empty($livro->imagem_capa) && !empty($itemEncontrado['imagem_capa'])) {
                        $livro->imagem_capa = $itemEncontrado['imagem_capa'];
                    }

                    if ((empty($livro->isbn) || Str::startsWith((string) $livro->isbn, 'SEM-ISBN-')) && !empty($itemEncontrado['isbn'])) {
                        $livro->isbn = $itemEncontrado['isbn'];
                    }

                    $editoraAtual = optional($livro->editora)->nome;
                    if (empty($editoraAtual) || $editoraAtual === 'Editora Desconhecida') {
                        $novaEditoraNome = trim((string) ($itemEncontrado['editora'] ?? ''));
                        if ($novaEditoraNome !== '' && $novaEditoraNome !== 'Editora Desconhecida') {
                            $editora = Editora::all()->first(fn($e) => mb_strtolower($e->nome) === mb_strtolower($novaEditoraNome));
                            if (!$editora) {
                                $editora = Editora::create([
                                    'nome' => $novaEditoraNome,
                                    'logotipo' => null,
                                ]);
                            }
                            $livro->editora_id = $editora->id;
                        }
                    }

                    $temAutoresReais = $livro->autores->isNotEmpty() && !($livro->autores->count() === 1 && $livro->autores->first()->nome === 'Autor Desconhecido');
                    if (!$temAutoresReais) {
                        $autoresNomes = (array) ($itemEncontrado['autores'] ?? []);
                        $autoresValidos = array_filter($autoresNomes, fn($a) => trim((string)$a) !== '' && trim((string)$a) !== 'Autor Desconhecido');
                        if (!empty($autoresValidos)) {
                            $autoresIds = [];
                            $todosAutores = Autor::all();
                            foreach ($autoresValidos as $nomeAutor) {
                                $nomeLimpo = trim((string) $nomeAutor);
                                $autor = $todosAutores->first(fn($a) => mb_strtolower($a->nome) === mb_strtolower($nomeLimpo));
                                if (!$autor) {
                                    $autor = Autor::create([
                                        'nome' => $nomeLimpo,
                                        'foto' => null,
                                    ]);
                                    $todosAutores->push($autor);
                                }
                                $autoresIds[] = $autor->id;
                            }
                            if (!empty($autoresIds)) {
                                $livro->autores()->sync($autoresIds);
                            }
                        }
                    }

                    $livro->save();
                });

                $sincronizados++;
                $this->line("✔ [Sincronizado] {$livro->nome}");
            } catch (Throwable $e) {
                $this->error("Erro ao sincronizar {$livro->nome}: {$e->getMessage()}");
            }
        }

        $this->info("Processamento concluído: {$sincronizados} sincronizados, {$semCorrespondencia} sem correspondência.");

        return Command::SUCCESS;
    }
}