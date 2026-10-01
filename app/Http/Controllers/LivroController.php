<?php

namespace App\Http\Controllers;

use App\Exports\LivrosExport;
use App\Models\Autor;
use App\Models\Editora;
use App\Models\Livro;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;

class LivroController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $editoraFiltro = $request->input('editora_id');
        $autorFiltro = $request->input('autor_id');

        $livros = Livro::with(['editora', 'autores'])->get();

        if ($search) {
            $livros = $livros->filter(function ($livro) use ($search) {
                return stripos($livro->nome, $search) !== false 
                    || stripos($livro->isbn, $search) !== false
                    || stripos($livro->editora->nome ?? '', $search) !== false
                    || $livro->autores->contains(fn($a) => stripos($a->nome, $search) !== false);
            });
        }

        if ($editoraFiltro) {
            $livros = $livros->where('editora_id', $editoraFiltro);
        }

        if ($autorFiltro) {
            $livros = $livros->filter(function ($livro) use ($autorFiltro) {
                return $livro->autores->contains('id', $autorFiltro);
            });
        }

        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'asc');

        $livros = $direction === 'asc'
            ? $livros->sortBy($sort)
            : $livros->sortByDesc($sort);

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

        $editoras = Editora::all();
        $autores = Autor::all();

        return view('livros.index', compact('livrosPaginados', 'editoras', 'autores', 'search', 'editoraFiltro', 'autorFiltro', 'sort', 'direction'));
    }

    public function create()
    {
        $editoras = Editora::all();
        $autores = Autor::all();

        return view('livros.create', compact('editoras', 'autores'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'isbn' => 'required|string|max:255',
            'nome' => 'required|string|max:255',
            'editora_id' => 'required|exists:editoras,id',
            'autores' => 'required|array|min:1',
            'autores.*' => 'exists:autores,id',
            'bibliografia' => 'nullable|string',
            'imagem_capa' => 'nullable|string|max:255',
            'preco' => 'required|numeric|min:0',
        ]);

        $livro = Livro::create([
            'isbn' => $validated['isbn'],
            'nome' => $validated['nome'],
            'editora_id' => $validated['editora_id'],
            'bibliografia' => $validated['bibliografia'] ?? '',
            'imagem_capa' => $validated['imagem_capa'] ?? '',
            'preco' => $validated['preco'],
        ]);

        $livro->autores()->attach($validated['autores']);

        return redirect()->route('livros.index')->with('success', 'livro adicionado com sucesso!');
    }

    public function edit(Livro $livro)
    {
        $editoras = Editora::all();
        $autores = Autor::all();
        $livro->load('autores');

        return view('livros.edit', compact('livro', 'editoras', 'autores'));
    }

    public function update(Request $request, Livro $livro)
    {
        $validated = $request->validate([
            'isbn' => 'required|string|max:255',
            'nome' => 'required|string|max:255',
            'editora_id' => 'required|exists:editoras,id',
            'autores' => 'required|array|min:1',
            'autores.*' => 'exists:autores,id',
            'bibliografia' => 'nullable|string',
            'imagem_capa' => 'nullable|string|max:255',
            'preco' => 'required|numeric|min:0',
        ]);

        $livro->update([
            'isbn' => $validated['isbn'],
            'nome' => $validated['nome'],
            'editora_id' => $validated['editora_id'],
            'bibliografia' => $validated['bibliografia'] ?? '',
            'imagem_capa' => $validated['imagem_capa'] ?? '',
            'preco' => $validated['preco'],
        ]);

        $livro->autores()->sync($validated['autores']);

        return redirect()->route('livros.index')->with('success', 'livro atualizado com sucesso!');
    }

    public function destroy(Livro $livro)
    {
        $livro->autores()->detach();
        $livro->delete();

        return redirect()->route('livros.index')->with('success', 'livro eliminado com sucesso!');
    }

    public function export()
    {
        return Excel::download(new LivrosExport, 'livros.xlsx');
    }
}