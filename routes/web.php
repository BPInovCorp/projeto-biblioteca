<?php

use App\Http\Controllers\AdminComentarioController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AlertaController;
use App\Http\Controllers\AutorController;
use App\Http\Controllers\ComentarioController;
use App\Http\Controllers\EditoraController;
use App\Http\Controllers\GoogleBooksController;
use App\Http\Controllers\LivroController;
use App\Http\Controllers\RequisicaoController;
use App\Models\Autor;
use App\Models\Comentario;
use App\Models\Editora;
use App\Models\Livro;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    $userId = $request->user()?->id;

    $query = Livro::with([
        'editora',
        'autores',
        'requisicoes',
        'comentarios' => fn($q) => $q->where('status', 'ativo')->with('user')->latest()->take(10),
        'alertas' => fn($q) => $q->where('user_id', $userId)->whereNull('notificado_at'),
    ]);

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

    $precoMin = $request->input('preco_min');
    $precoMax = $request->input('preco_max');
    if ($precoMin !== null && $precoMin !== '') {
        $livros = $livros->filter(fn($l) => (float)$l->preco >= (float)$precoMin);
    }
    if ($precoMax !== null && $precoMax !== '') {
        $livros = $livros->filter(fn($l) => (float)$l->preco <= (float)$precoMax);
    }

    $pesquisa = $request->input('search');
    if ($pesquisa) {
        $livros = $livros->filter(function ($livro) use ($pesquisa) {
            return stripos($livro->nome, $pesquisa) !== false
                || stripos($livro->isbn, $pesquisa) !== false
                || stripos(optional($livro->editora)->nome ?? '', $pesquisa) !== false
                || $livro->autores->contains(fn($a) => stripos($a->nome, $pesquisa) !== false);
        });
    }

    $ordenar = $request->input('ordenar', 'livro_nome_asc');
    if ($ordenar === 'livro_nome_asc' || $ordenar === 'nome_asc') {
        $livros = $livros->sortBy('nome');
    } elseif ($ordenar === 'livro_nome_desc' || $ordenar === 'nome_desc') {
        $livros = $livros->sortByDesc('nome');
    } elseif ($ordenar === 'livro_isbn_asc' || $ordenar === 'isbn_asc') {
        $livros = $livros->sortBy('isbn');
    } elseif ($ordenar === 'livro_isbn_desc' || $ordenar === 'isbn_desc') {
        $livros = $livros->sortByDesc('isbn');
    } elseif ($ordenar === 'autor_nome_asc') {
        $livros = $livros->sortBy(fn($l) => $l->autores->pluck('nome')->implode(', '));
    } elseif ($ordenar === 'autor_nome_desc') {
        $livros = $livros->sortByDesc(fn($l) => $l->autores->pluck('nome')->implode(', '));
    } elseif ($ordenar === 'editora_nome_asc') {
        $livros = $livros->sortBy(fn($l) => $l->editora->nome ?? '');
    } elseif ($ordenar === 'editora_nome_desc') {
        $livros = $livros->sortByDesc(fn($l) => $l->editora->nome ?? '');
    }

    $perPage = 24;
    $currentPage = LengthAwarePaginator::resolveCurrentPage();
    $currentItems = $livros->slice(($currentPage - 1) * $perPage, $perPage)->values();

    $livrosPaginados = new LengthAwarePaginator(
        $currentItems,
        $livros->count(),
        $perPage,
        $currentPage,
        ['path' => $request->url(), 'query' => $request->query()]
    );

    $autores = Autor::all()->sortBy('nome');
    $editoras = Editora::all()->sortBy('nome');

    return view('welcome', compact('livrosPaginados', 'autores', 'editoras'));
});

Route::get('/livros/{livro}/detalhes', [LivroController::class, 'detalhes'])->name('livros.detalhes');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
])->group(function () {
    Route::get('/dashboard', function () {
        if (!request()->user()?->isAdmin()) {
            return redirect()->route('profile.show');
        }

        $totalLivros = Livro::count();
        $totalAutores = Autor::count();
        $totalEditoras = Editora::count();
        $totalComentariosSuspensos = Comentario::where('status', 'suspenso')->count();

        return view('dashboard', compact(
            'totalLivros',
            'totalAutores',
            'totalEditoras',
            'totalComentariosSuspensos'
        ));
    })->name('dashboard');

    Route::get('/requisicoes', [RequisicaoController::class, 'index'])->name('requisicoes.index');
    Route::post('/requisicoes', [RequisicaoController::class, 'store'])->name('requisicoes.store');
    Route::post('/requisicoes/{requisicao}/cancelar', [RequisicaoController::class, 'cancelar'])->name('requisicoes.cancelar');
    Route::post('/comentarios', [ComentarioController::class, 'store'])->name('comentarios.store');
        Route::post('/alertas', [AlertaController::class, 'store'])->name('alertas.store');

    Route::middleware(\App\Http\Middleware\AdminMiddleware::class)->group(function () {
        Route::resource('editoras', EditoraController::class)->except(['show']);
        Route::resource('autores', AutorController::class)->parameters(['autores' => 'autor'])->except(['show']);
        Route::get('livros/exportar', [LivroController::class, 'export'])->name('livros.export');
        Route::resource('livros', LivroController::class)->except(['show']);
        Route::get('admin/utilizadores/criar', [AdminUserController::class, 'create'])->name('admin.users.create');
        Route::post('admin/utilizadores', [AdminUserController::class, 'store'])->name('admin.users.store');
        Route::post('requisicoes/{requisicao}/devolver', [RequisicaoController::class, 'devolver'])->name('requisicoes.devolver');
        Route::get('admin/livros/importar', [GoogleBooksController::class, 'index'])->name('livros.importar');
        Route::get('admin/livros/importar/pesquisar', [GoogleBooksController::class, 'search'])->name('livros.importar.search');
        Route::post('admin/livros/importar', [GoogleBooksController::class, 'store'])->name('livros.importar.store');
        Route::post('admin/livros/{livro}/sincronizar', [GoogleBooksController::class, 'sincronizar'])->name('livros.sincronizar');
        Route::get('admin/comentarios', [AdminComentarioController::class, 'index'])->name('admin.comentarios.index');
        Route::post('admin/comentarios/{comentario}/aprovar', [AdminComentarioController::class, 'aprovar'])->name('admin.comentarios.aprovar');
        Route::post('admin/comentarios/{comentario}/recusar', [AdminComentarioController::class, 'recusar'])->name('admin.comentarios.recusar');
    });
});