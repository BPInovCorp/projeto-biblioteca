<?php

namespace App\Http\Controllers;

use App\Models\Livro;
use App\Models\Requisicao;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RequisicaoController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = Requisicao::with(['user', 'livro'])->latest('id');

        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        $requisicoes = $query->paginate(15)->withQueryString();

        $kpisQuery = Requisicao::query();
        if (!$user->isAdmin()) {
            $kpisQuery->where('user_id', $user->id);
        }

        $totalAtivas = (clone $kpisQuery)->where('status', 'ativa')->count();
        $ultimos30Dias = (clone $kpisQuery)->where('data_requisicao', '>=', now()->subDays(30)->toDateString())->count();
        $entreguesHoje = (clone $kpisQuery)->where('status', 'entregue')->whereDate('data_devolucao', today())->count();

        return view('requisicoes.index', compact('requisicoes', 'totalAtivas', 'ultimos30Dias', 'entreguesHoje'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'livro_id' => ['required', 'exists:livros,id'],
        ]);

        $user = $request->user();

        return DB::transaction(function () use ($validated, $user) {
            $livro = Livro::lockForUpdate()->findOrFail($validated['livro_id']);

            if (!$livro->isDisponivel()) {
                return redirect()->back()->with('error', __('Livro indisponível para requisição.'));
            }

            $requisicoesAtivasCount = Requisicao::where('user_id', $user->id)
                ->where('status', 'ativa')
                ->lockForUpdate()
                ->count();

            if ($requisicoesAtivasCount >= 3) {
                return redirect()->back()->with('error', __('Atingiu o limite máximo de 3 livros requisitados em simultâneo.'));
            }

            $ultimoNumero = Requisicao::max('numero_sequencial') ?? 0;
            $proximoNumero = $ultimoNumero + 1;

            $fotoSnapshot = $user->profile_photo_path ?? $user->profile_photo_url;

            Requisicao::create([
                'numero_sequencial' => $proximoNumero,
                'user_id' => $user->id,
                'livro_id' => $livro->id,
                'foto_cidadao' => $fotoSnapshot,
                'data_requisicao' => now()->toDateString(),
                'data_fim_prevista' => now()->addDays(5)->toDateString(),
                'status' => 'ativa',
            ]);

            return redirect()->back()->with('status', __('Requisição realizada com sucesso! Número de registo: :numero', ['numero' => $proximoNumero]));
        });
    }
}