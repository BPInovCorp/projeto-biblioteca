<?php

namespace App\Http\Controllers;

use App\Models\Autor;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AutorController extends Controller
{
    protected AuditoriaService $auditoriaService;

    public function __construct(AuditoriaService $auditoriaService)
    {
        $this->auditoriaService = $auditoriaService;
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $autores = Autor::all();

        if ($search) {
            $autores = $autores->filter(function ($autor) use ($search) {
                return stripos($autor->nome, $search) !== false;
            });
        }

        $allowedSorts = ['id', 'nome'];
        $sort = in_array($request->input('sort'), $allowedSorts, true) ? $request->input('sort') : 'id';
        $direction = $request->input('direction') === 'desc' ? 'desc' : 'asc';

        $autores = $direction === 'asc'
            ? $autores->sortBy($sort)
            : $autores->sortByDesc($sort);

        $perPage = 24;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $autores->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $autoresPaginados = new LengthAwarePaginator(
            $currentItems,
            $autores->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('autores.index', compact('autoresPaginados', 'search', 'sort', 'direction'));
    }

    public function create()
    {
        return view('autores.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'foto' => 'nullable|string|max:255',
        ]);

        $autor = Autor::create($validated);

        $this->auditoriaService->registar(
            'Autores',
            $autor->id,
            "Criou o autor '{$autor->nome}'",
            $request->user()
        );

        return redirect()->route('autores.index')->with('success', 'Autor adicionado com sucesso!');
    }

    public function edit(Autor $autor)
    {
        return view('autores.edit', compact('autor'));
    }

    public function update(Request $request, Autor $autor)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'foto' => 'nullable|string|max:255',
        ]);

        $autor->update($validated);

        $this->auditoriaService->registar(
            'Autores',
            $autor->id,
            "Atualizou os dados do autor '{$autor->nome}'",
            $request->user()
        );

        return redirect()->route('autores.index')->with('success', 'Autor atualizado com sucesso!');
    }

    public function destroy(Autor $autor)
    {
        $autorId = $autor->id;
        $autorNome = $autor->nome;

        $autor->delete();

        $this->auditoriaService->registar(
            'Autores',
            $autorId,
            "Eliminou o autor '{$autorNome}'",
            request()->user()
        );

        return redirect()->route('autores.index')->with('success', 'Autor eliminado com sucesso!');
    }
}