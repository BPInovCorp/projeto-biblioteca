<?php

namespace App\Http\Controllers;

use App\Models\Editora;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class EditoraController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $editoras = Editora::all();

        if ($search) {
            $editoras = $editoras->filter(function ($editora) use ($search) {
                return stripos($editora->nome, $search) !== false;
            });
        }

        $allowedSorts = ['id', 'nome'];
        $sort = in_array($request->input('sort'), $allowedSorts, true) ? $request->input('sort') : 'id';
        $direction = $request->input('direction') === 'desc' ? 'desc' : 'asc';

        $editoras = $direction === 'asc'
            ? $editoras->sortBy($sort)
            : $editoras->sortByDesc($sort);

        $perPage = 24;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $editoras->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $editorasPaginadas = new LengthAwarePaginator(
            $currentItems,
            $editoras->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('editoras.index', compact('editorasPaginadas', 'search', 'sort', 'direction'));
    }

    public function create()
    {
        return view('editoras.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'logotipo' => 'nullable|string|max:255',
        ]);

        Editora::create($validated);

        return redirect()->route('editoras.index')->with('success', 'Editora adicionada com sucesso!');
    }

    public function edit(Editora $editora)
    {
        return view('editoras.edit', compact('editora'));
    }

    public function update(Request $request, Editora $editora)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'logotipo' => 'nullable|string|max:255',
        ]);

        $editora->update($validated);

        return redirect()->route('editoras.index')->with('success', 'editora atualizada com sucesso!');
    }

    public function destroy(Editora $editora)
    {
        $editora->delete();

        return redirect()->route('editoras.index')->with('success', 'editora eliminada com sucesso!');
    }
}