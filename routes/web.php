<?php

use App\Http\Controllers\AutorController;
use App\Http\Controllers\EditoraController;
use App\Http\Controllers\LivroController;
use App\Models\Autor;
use App\Models\Editora;
use App\Models\Livro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    $query = Livro::with(['editora', 'autores']);

    $autoresSelecionados = $request->input('autores', []);
    if (!empty($autoresSelecionados)) {
        $query->whereHas('autores', function ($q) use ($autoresSelecionados) {
            $q->whereIn('autores.id', $autoresSelecionados);
        });
    }

    $editorasSelecionadas = $request->input('editoras', []);
    if (!empty($editorasSelecionadas)) {
        $query->whereIn('editora_id', $editorasSelecionadas);
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
])->group(function () {
    Route::get('/dashboard', function () {
        if (!request()->user()?->isAdmin()) {
            return redirect()->route('profile.show');
        }

        return view('dashboard');
    })->name('dashboard');

    Route::middleware(\App\Http\Middleware\AdminMiddleware::class)->group(function () {
        Route::resource('editoras', EditoraController::class)->except(['show']);
        Route::resource('autores', AutorController::class)->parameters(['autores' => 'autor'])->except(['show']);
        Route::get('livros/exportar', [LivroController::class, 'export'])->name('livros.export');
        Route::resource('livros', LivroController::class)->except(['show']);
    });
});
