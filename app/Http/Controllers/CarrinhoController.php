<?php

namespace App\Http\Controllers;

use App\Models\CarrinhoItem;
use App\Models\Livro;
use App\Services\CarrinhoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class CarrinhoController extends Controller
{
    public function __construct(
        protected CarrinhoService $carrinhoService
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $itens = $this->carrinhoService->obterItens($user);
        $totalCentimos = $this->carrinhoService->calcularTotalCentimos($user);
        $totalItens = $this->carrinhoService->contarItens($user);
        $totalPrecosCentimos = (int) $itens->sum(fn ($item) => $item->livro->preco_centimos);

        return view('carrinho.index', compact('itens', 'totalCentimos', 'totalItens', 'totalPrecosCentimos'));
    }

    public function adicionar(Request $request, Livro $livro): JsonResponse|RedirectResponse
    {
        $dados = $request->validate([
            'quantidade' => ['nullable', 'integer', 'min:1'],
        ]);

        $quantidade = (int) ($dados['quantidade'] ?? 1);

        try {
            $item = $this->carrinhoService->adicionarLivro($request->user(), $livro, $quantidade);
        } catch (InvalidArgumentException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'mensagem' => $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['carrinho' => $e->getMessage()]);
        }

        $totalItens = $this->carrinhoService->contarItens($request->user());

        if ($request->expectsJson()) {
            return response()->json([
                'mensagem' => 'Livro adicionado ao carrinho com sucesso.',
                'item_id' => $item->id,
                'total_itens' => $totalItens,
            ]);
        }

        return back()->with('status', 'Livro adicionado ao carrinho com sucesso.');
    }

    public function atualizar(Request $request, CarrinhoItem $item): JsonResponse|RedirectResponse
    {
        abort_if($item->user_id !== $request->user()->id, 403);

        $dados = $request->validate([
            'quantidade' => ['required', 'integer', 'min:0'],
        ]);

        try {
            $itemAtualizado = $this->carrinhoService->atualizarQuantidade(
                $request->user(),
                $item->id,
                (int) $dados['quantidade']
            );
        } catch (InvalidArgumentException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'mensagem' => $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['carrinho' => $e->getMessage()]);
        }

        $totalCentimos = $this->carrinhoService->calcularTotalCentimos($request->user());
        $totalItens = $this->carrinhoService->contarItens($request->user());

        if ($request->expectsJson()) {
            return response()->json([
                'mensagem' => 'Carrinho atualizado com sucesso.',
                'removido' => $itemAtualizado === null,
                'total_centimos' => $totalCentimos,
                'total_formatado' => number_format($totalCentimos / 100, 2, ',', '.') . ' €',
                'total_itens' => $totalItens,
            ]);
        }

        return redirect()->route('carrinho.index')->with('status', 'Carrinho atualizado com sucesso.');
    }

    public function remover(Request $request, CarrinhoItem $item): JsonResponse|RedirectResponse
    {
        abort_if($item->user_id !== $request->user()->id, 403);

        $this->carrinhoService->removerItem($request->user(), $item->id);

        $totalCentimos = $this->carrinhoService->calcularTotalCentimos($request->user());
        $totalItens = $this->carrinhoService->contarItens($request->user());

        if ($request->expectsJson()) {
            return response()->json([
                'mensagem' => 'Artigo removido do carrinho com sucesso.',
                'total_centimos' => $totalCentimos,
                'total_formatado' => number_format($totalCentimos / 100, 2, ',', '.') . ' €',
                'total_itens' => $totalItens,
            ]);
        }

        return redirect()->route('carrinho.index')->with('status', 'Artigo removido do carrinho com sucesso.');
    }

    public function limpar(Request $request): JsonResponse|RedirectResponse
    {
        $this->carrinhoService->limpar($request->user());

        if ($request->expectsJson()) {
            return response()->json([
                'mensagem' => 'Carrinho esvaziado com sucesso.',
                'total_centimos' => 0,
                'total_formatado' => '0,00 €',
                'total_itens' => 0,
            ]);
        }

        return redirect()->route('carrinho.index')->with('status', 'Carrinho esvaziado com sucesso.');
    }
}