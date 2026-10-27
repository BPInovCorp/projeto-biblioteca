<?php

namespace App\Http\Controllers;

use App\Models\Encomenda;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminEncomendaController extends Controller
{
    public function index(Request $request): View
    {
        $filtro = $request->input('filtro', 'todas');
        $pesquisa = $request->input('search');

        $query = Encomenda::with(['user', 'items.livro']);

        if ($filtro === 'pagas') {
            $query->where('estado_pagamento', Encomenda::PAGAMENTO_PAGO);
        } elseif ($filtro === 'pendentes') {
            $query->where('estado_pagamento', Encomenda::PAGAMENTO_PENDENTE);
        }

        if (! empty($pesquisa)) {
            $query->where(function ($q) use ($pesquisa) {
                $q->where('numero', 'like', "%{$pesquisa}%")
                    ->orWhere('nome_destinatario', 'like', "%{$pesquisa}%")
                    ->orWhereHas('user', function ($userQuery) use ($pesquisa) {
                        $userQuery->where('name', 'like', "%{$pesquisa}%")
                            ->orWhere('email', 'like', "%{$pesquisa}%");
                    });
            });
        }

        $totalTodas = Encomenda::count();
        $totalPagas = Encomenda::where('estado_pagamento', Encomenda::PAGAMENTO_PAGO)->count();
        $totalPendentes = Encomenda::where('estado_pagamento', Encomenda::PAGAMENTO_PENDENTE)->count();

        $encomendas = $query->latest()->paginate(15)->withQueryString();

        return view('admin.encomendas.index', compact(
            'encomendas',
            'totalTodas',
            'totalPagas',
            'totalPendentes',
            'filtro',
            'pesquisa'
        ));
    }

    public function show(Encomenda $encomenda): View
    {
        $encomenda->load([
            'user',
            'items.livro.autores',
            'items.livro.editora',
        ]);

        return view('admin.encomendas.show', compact('encomenda'));
    }
}