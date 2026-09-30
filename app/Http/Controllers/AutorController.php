<?php

namespace App\Http\Controllers;

use App\Models\Autor;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AutorController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $autores = Autor::all();

        if ($search) {
            $autores = $autores->filter(function ($autor) use ($search) {
                return stripos($autor->nome, $search) !== false;
            });
        }

        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'asc');

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

        Autor::create($validated);

        return redirect()->route('autores.index');
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

        return redirect()->route('autores.index');
    }

    public function destroy(Autor $autor)
    {
        $autor->delete();

        return redirect()->route('autores.index');
    }
}