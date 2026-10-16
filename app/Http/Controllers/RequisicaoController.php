<?php

namespace App\Http\Controllers;

use App\Mail\RequisicaoConfirmadaMail;
use App\Models\Livro;
use App\Models\Requisicao;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class RequisicaoController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = Requisicao::with(['user.requisicoes.livro', 'livro', 'comentario'])->latest('id');

        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        $requisicoes = $query->paginate(15)->withQueryString();

        $kpisQuery = Requisicao::query();
        if (!$user->isAdmin()) {
            $kpisQuery->where('user_id', $user->id);
        }

        $totalAtivas = (clone $kpisQuery)->where('status', 'ativa')->count();
        $ultimos30Dias = (clone $kpisQuery)->where('status', '!=', 'cancelada')->where('data_requisicao', '>=', now()->subDays(30)->toDateString())->count();
        $entreguesHoje = (clone $kpisQuery)->where('status', 'entregue')->whereDate('data_devolucao', today())->count();

        return view('requisicoes.index', compact('requisicoes', 'totalAtivas', 'ultimos30Dias', 'entreguesHoje'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'livro_id' => ['required', 'exists:livros,id'],
        ]);

        $user = $request->user();

        $requisicao = retry(3, function () use ($validated, $user) {
            return DB::transaction(function () use ($validated, $user) {
                $livro = Livro::lockForUpdate()->findOrFail($validated['livro_id']);

                if (!$livro->isDisponivel()) {
                    return null;
                }

                $requisicoesAtivasCount = Requisicao::where('user_id', $user->id)
                    ->where('status', 'ativa')
                    ->lockForUpdate()
                    ->count();

                if ($requisicoesAtivasCount >= 3) {
                    return false;
                }

                $ultimoNumero = Requisicao::max('numero_sequencial') ?? 0;
                $proximoNumero = $ultimoNumero + 1;

                $fotoSnapshot = $user->profile_photo_url;

                return Requisicao::create([
                    'numero_sequencial' => $proximoNumero,
                    'user_id' => $user->id,
                    'livro_id' => $livro->id,
                    'foto_cidadao' => $fotoSnapshot,
                    'data_requisicao' => now()->toDateString(),
                    'data_fim_prevista' => now()->addDays(5)->toDateString(),
                    'status' => 'ativa',
                ]);
            });
        }, 100);

        if ($requisicao === null) {
            return redirect()->back()->with('error', __('Livro indisponível para requisição.'));
        }

        if ($requisicao === false) {
            return redirect()->back()->with('error', __('Atingiu o limite máximo de 3 livros requisitados em simultâneo.'));
        }

        $requisicao->load(['user', 'livro.editora', 'livro.autores']);

        try {
            Mail::to($user->email)->send(new RequisicaoConfirmadaMail($requisicao, false));
        } catch (Throwable $e) {
            Log::error('Falha ao enviar email ao cidadão: ' . $e->getMessage());
        }

        try {
            $administradores = User::where('role', 'admin')->get();
            foreach ($administradores as $admin) {
                Mail::to($admin->email)->send(new RequisicaoConfirmadaMail($requisicao, true));
            }
        } catch (Throwable $e) {
            Log::error('Falha ao enviar email aos administradores: ' . $e->getMessage());
        }

        return redirect()->back()->with('status', __('Requisição realizada com sucesso! Número de registo: :numero', ['numero' => $requisicao->numero_sequencial]));
    }

    public function devolver(Request $request, Requisicao $requisicao): RedirectResponse
    {
        $request->validate([
            'data_devolucao' => [
                'nullable',
                'date',
                'after_or_equal:' . $requisicao->data_requisicao->toDateString(),
                'before_or_equal:today',
            ],
        ]);

        if ($requisicao->status === 'entregue') {
            return redirect()->back()->with('error', __('Esta requisição já se encontra entregue.'));
        }

        $dataDevolucao = $request->filled('data_devolucao')
            ? Carbon::parse($request->input('data_devolucao'))
            : today();

        $diasDecorridos = max(0, (int) Carbon::parse($requisicao->data_requisicao)->diffInDays($dataDevolucao));

        $requisicao->update([
            'data_devolucao' => $dataDevolucao->toDateString(),
            'dias_decorridos' => $diasDecorridos,
            'status' => 'entregue',
        ]);

        return redirect()->back()->with('status', __('Livro rececionado e devolvido com sucesso.'));
    }

    public function cancelar(Request $request, Requisicao $requisicao): RedirectResponse
    {
        $user = $request->user();

        if ($requisicao->status !== 'ativa') {
            return redirect()->back()->with('error', __('Apenas é possível cancelar requisições ativas.'));
        }

        if (!$user->isAdmin() && $requisicao->user_id !== $user->id) {
            return redirect()->back()->with('error', __('Não tem autorização para cancelar esta requisição.'));
        }

        $requisicao->update([
            'status' => 'cancelada',
        ]);

        return redirect()->back()->with('status', __('Requisição cancelada com sucesso.'));
    }
}