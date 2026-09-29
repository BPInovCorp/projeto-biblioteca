<?php

use App\Models\Autor;
use App\Models\Editora;
use App\Models\Livro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    $query = Livro::with(['editora', 'autores']);

    $excluirAutores = $request->input('excluir_autores', []);
    if (!empty($excluirAutores)) {
        $query->whereDoesntHave('autores', function ($q) use ($excluirAutores) {
            $q->whereIn('autores.id', $excluirAutores);
        });
    }

    $excluirEditoras = $request->input('excluir_editoras', []);
    if (!empty($excluirEditoras)) {
        $query->whereNotIn('editora_id', $excluirEditoras);
    }

    $livros = $query->get();

    $pesquisa = $request->input('search');
    if ($pesquisa) {
        $livros = $livros->filter(function ($livro) use ($pesquisa) {
            return stripos($livro->nome, $pesquisa) !== false
                || stripos($livro->isbn, $pesquisa) !== false
                || stripos(optional($livro->editora)->nome ?? '', $pesquisa) !== false
                || $livro->autores->contains(fn($a) => stripos($a->nome, $pesquisa) !== false);
        });
    }

    $ordenar = $request->input('ordenar', 'nome_asc');
    if ($ordenar === 'nome_asc') {
        $livros = $livros->sortBy('nome');
    } elseif ($ordenar === 'nome_desc') {
        $livros = $livros->sortByDesc('nome');
    } elseif ($ordenar === 'isbn_asc') {
        $livros = $livros->sortBy('isbn');
    } elseif ($ordenar === 'isbn_desc') {
        $livros = $livros->sortByDesc('isbn');
    }

    $autores = Autor::all();
    $editoras = Editora::all();

    return view('welcome', compact('livros', 'autores', 'editoras'));
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});