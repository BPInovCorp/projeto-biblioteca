<?php

namespace App\Http\Controllers;

use App\Models\Autor;
use App\Models\Editora;
use App\Models\Livro;
use App\Services\GoogleBooksService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class GoogleBooksController extends Controller
{
    public function index(): View
    {
        return view('livros.import', [
            'query' => '',
            'tipo' => 'geral',
            'page' => 1,
            'totalPages' => 0,
            'totalpages' => 0,
            'items' => [],
            'total' => 0,
            'pesquisado' => false,
            'erro' => null,
        ]);
    }

    public function search(Request $request, GoogleBooksService $googleBooks): View
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'min:2', 'max:255'],
            'tipo' => ['nullable', 'string', 'in:geral,titulo,autor,isbn,editora'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = trim($validated['query']);
        $tipo = $validated['tipo'] ?? 'geral';
        $page = (int) ($validated['page'] ?? 1);
        $perPage = 12;
        $startIndex = ($page - 1) * $perPage;

        $resultado = $googleBooks->search($query, $tipo, $perPage, $startIndex);
        $total = (int) ($resultado['total'] ?? 0);
        $totalPages = $total > 0 ? (int) min(50, ceil($total / $perPage)) : 0;

        $livrosLocais = Livro::all();

        $isbnsLocais = $livrosLocais->pluck('isbn')
            ->filter()
            ->map(fn($isbn) => preg_replace('/[^0-9xX]/', '', (string) $isbn))
            ->values()
            ->all();

        $titulosLocais = $livrosLocais->pluck('nome')
            ->filter()
            ->map(fn($nome) => trim(mb_strtolower((string) $nome)))
            ->values()
            ->all();

        $itemsComStatus = array_map(function ($item) use ($isbnsLocais, $titulosLocais) {
            $jaExiste = false;

            if (!empty($item['isbn'])) {
                $cleanIsbn = preg_replace('/[^0-9xX]/', '', (string) $item['isbn']);
                if (in_array($cleanIsbn, $isbnsLocais, true)) {
                    $jaExiste = true;
                }
            }

            if (!$jaExiste && !empty($item['nome'])) {
                $cleanTitulo = trim(mb_strtolower((string) $item['nome']));
                if (in_array($cleanTitulo, $titulosLocais, true)) {
                    $jaExiste = true;
                }
            }

            $item['ja_no_acervo'] = $jaExiste;

            return $item;
        }, $resultado['items']);

        return view('livros.import', [
            'query' => $query,
            'tipo' => $tipo,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalpages' => $totalPages,
            'items' => $itemsComStatus,
            'total' => $total,
            'pesquisado' => true,
            'erro' => $resultado['erro'] ?? null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'editora' => ['nullable', 'string', 'max:255'],
            'autores' => ['nullable', 'array'],
            'isbn' => ['nullable', 'string', 'max:50'],
            'bibliografia' => ['nullable', 'string'],
            'imagem_capa' => ['nullable', 'string'],
            'preco' => ['nullable', 'numeric', 'min:0'],
        ]);

        $livrosLocais = Livro::all();

        if (!empty($validated['isbn'])) {
            $cleanIsbn = preg_replace('/[^0-9xX]/', '', (string) $validated['isbn']);
            $existeIsbn = $livrosLocais->first(function ($l) use ($cleanIsbn) {
                return preg_replace('/[^0-9xX]/', '', (string) $l->isbn) === $cleanIsbn;
            });

            if ($existeIsbn) {
                return redirect()->back()->with('error', __('Esta obra já se encontra registada no acervo da biblioteca.'));
            }
        }

        return DB::transaction(function () use ($validated) {
            $editoraNome = trim((string) ($validated['editora'] ?? '')) ?: 'Editora Desconhecida';
            $todasEditoras = Editora::all();
            $editora = $this->encontrarEditora($editoraNome, $todasEditoras);

            if (!$editora) {
                $editora = Editora::create([
                    'nome' => $editoraNome,
                    'logotipo' => null,
                ]);
                $todasEditoras->push($editora);
            }

            $autoresNomes = (array) ($validated['autores'] ?? []);
            if (empty($autoresNomes)) {
                $autoresNomes = ['Autor Desconhecido'];
            }

            $autoresIds = [];
            $todosAutores = Autor::all();
            foreach ($autoresNomes as $nomeAutor) {
                $nomeLimpo = trim((string) $nomeAutor);
                if ($nomeLimpo === '') {
                    continue;
                }

                $autor = $this->encontrarAutor($nomeLimpo, $todosAutores);
                if (!$autor) {
                    $autor = Autor::create([
                        'nome' => $nomeLimpo,
                        'foto' => null,
                    ]);
                    $todosAutores->push($autor);
                }
                $autoresIds[] = $autor->id;
            }

            $capaFinal = !empty($validated['imagem_capa']) ? trim((string) $validated['imagem_capa']) : null;

            $preco = isset($validated['preco']) && is_numeric($validated['preco']) && (float) $validated['preco'] > 0
                ? number_format((float) $validated['preco'], 2, '.', '')
                : 'Preço sob consulta';

            $isbn = !empty($validated['isbn'])
                ? trim((string) $validated['isbn'])
                : 'SEM-ISBN-' . strtoupper(Str::random(8));

            $livro = Livro::create([
                'editora_id' => $editora->id,
                'isbn' => $isbn,
                'nome' => $validated['nome'],
                'bibliografia' => $validated['bibliografia'] ?? null,
                'imagem_capa' => $capaFinal,
                'preco' => $preco,
            ]);

            if (!empty($autoresIds)) {
                $livro->autores()->sync($autoresIds);
            }

            return redirect()->route('livros.index')->with('success', __('Livro ":nome" importado com sucesso para o acervo da biblioteca.', ['nome' => $livro->nome]));
        });
    }

    public function sincronizar(Livro $livro, GoogleBooksService $googleBooks): RedirectResponse
    {
        $itemEncontrado = null;

        $isbnLimpo = preg_replace('/[^0-9xX]/', '', (string)$livro->isbn);
        if (!empty($isbnLimpo) && !Str::startsWith((string)$livro->isbn, 'SEM-ISBN-')) {
            $buscaIsbn = $googleBooks->search($isbnLimpo, 'isbn', 5);
            if (!empty($buscaIsbn['items'])) {
                $itemEncontrado = $buscaIsbn['items'][0];
            }
        }

        if (!$itemEncontrado && !empty($livro->nome)) {
            $buscaTitulo = $googleBooks->search($livro->nome, 'titulo', 5);
            if (!empty($buscaTitulo['items'])) {
                $itemEncontrado = $buscaTitulo['items'][0];
            }
        }

        if (!$itemEncontrado && !empty($livro->nome)) {
            $buscaGeral = $googleBooks->search($livro->nome, 'geral', 5);
            if (!empty($buscaGeral['items'])) {
                $itemEncontrado = $buscaGeral['items'][0];
            }
        }

        if (!$itemEncontrado) {
            return redirect()->back()->with('error', __('Nenhuma correspondência encontrada na Google Books para este livro.'));
        }

        DB::transaction(function () use ($livro, $itemEncontrado) {
            if (empty($livro->bibliografia) && !empty($itemEncontrado['bibliografia'])) {
                $livro->bibliografia = $itemEncontrado['bibliografia'];
            }

            if (empty($livro->imagem_capa) && !empty($itemEncontrado['imagem_capa'])) {
                $livro->imagem_capa = $itemEncontrado['imagem_capa'];
            }

            if (Str::startsWith((string)$livro->isbn, 'SEM-ISBN-') && !empty($itemEncontrado['isbn'])) {
                $livro->isbn = $itemEncontrado['isbn'];
            }

            $editoraAtualNome = optional($livro->editora)->nome;
            if (empty($editoraAtualNome) || $editoraAtualNome === 'Editora Desconhecida') {
                $novaEditoraNome = trim((string)($itemEncontrado['editora'] ?? '')) ?: 'Editora Desconhecida';
                $todasEditoras = Editora::all();
                $editora = $this->encontrarEditora($novaEditoraNome, $todasEditoras);

                if (!$editora) {
                    $editora = Editora::create([
                        'nome' => $novaEditoraNome,
                        'logotipo' => null,
                    ]);
                }
                $livro->editora_id = $editora->id;
            }

            if ($livro->autores->isEmpty() || ($livro->autores->count() === 1 && $livro->autores->first()->nome === 'Autor Desconhecido')) {
                $autoresNomes = (array) ($itemEncontrado['autores'] ?? []);
                if (!empty($autoresNomes)) {
                    $autoresIds = [];
                    $todosAutores = Autor::all();
                    foreach ($autoresNomes as $nomeAutor) {
                        $nomeLimpo = trim((string) $nomeAutor);
                        if ($nomeLimpo === '') {
                            continue;
                        }
                        $autor = $this->encontrarAutor($nomeLimpo, $todosAutores);
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

        return redirect()->back()->with('success', __('Metadados do livro ":nome" sincronizados com sucesso a partir da Google Books.', ['nome' => $livro->nome]));
    }

    protected function normalizarParaComparacao(string $texto): string
    {
        $limpo = Str::ascii($texto);
        $limpo = mb_strtolower($limpo);

        return preg_replace('/[^a-z0-9]/', '', $limpo) ?? '';
    }

    protected function encontrarAutor(string $nome, Collection $todosAutores): ?Autor
    {
        $nomeGoogle = trim($nome);
        if ($nomeGoogle === '') {
            return null;
        }

        $canonicoGoogle = $this->normalizarParaComparacao($nomeGoogle);
        if ($canonicoGoogle === '') {
            return null;
        }

        foreach ($todosAutores as $autor) {
            if (mb_strtolower(trim($autor->nome)) === mb_strtolower($nomeGoogle)) {
                return $autor;
            }

            $canonicoExistente = $this->normalizarParaComparacao($autor->nome);
            if ($canonicoExistente === $canonicoGoogle) {
                return $autor;
            }

            if (strlen($canonicoGoogle) > 8 && strlen($canonicoExistente) > 8) {
                similar_text($canonicoGoogle, $canonicoExistente, $percent);
                if ($percent >= 92) {
                    return $autor;
                }
            }
        }

        return null;
    }

    protected function encontrarEditora(string $nome, Collection $todasEditoras): ?Editora
    {
        $nomeGoogle = trim($nome);
        if ($nomeGoogle === '') {
            return null;
        }

        $canonicoGoogle = $this->normalizarParaComparacao($nomeGoogle);
        if ($canonicoGoogle === '') {
            return null;
        }

        foreach ($todasEditoras as $editora) {
            if (mb_strtolower(trim($editora->nome)) === mb_strtolower($nomeGoogle)) {
                return $editora;
            }

            $canonicoExistente = $this->normalizarParaComparacao($editora->nome);
            if ($canonicoExistente === $canonicoGoogle) {
                return $editora;
            }
        }

        $sufixos = ['sa', 'lda', 'limitada', 'inc', 'ltd', 'editora', 'edicoes', 'publishing'];
        $raizGoogle = $canonicoGoogle;
        foreach ($sufixos as $sufixo) {
            $raizGoogle = preg_replace('/' . $sufixo . '$/', '', $raizGoogle) ?? $raizGoogle;
            $raizGoogle = preg_replace('/^' . $sufixo . '/', '', $raizGoogle) ?? $raizGoogle;
        }

        if (strlen($raizGoogle) > 4) {
            foreach ($todasEditoras as $editora) {
                $raizExistente = $this->normalizarParaComparacao($editora->nome);
                foreach ($sufixos as $sufixo) {
                    $raizExistente = preg_replace('/' . $sufixo . '$/', '', $raizExistente) ?? $raizExistente;
                    $raizExistente = preg_replace('/^' . $sufixo . '/', '', $raizExistente) ?? $raizExistente;
                }

                if ($raizGoogle === $raizExistente) {
                    return $editora;
                }

                if (strlen($raizGoogle) > 7 && strlen($raizExistente) > 7) {
                    similar_text($raizGoogle, $raizExistente, $percent);
                    if ($percent >= 92) {
                        return $editora;
                    }
                }
            }
        }

        return null;
    }
}