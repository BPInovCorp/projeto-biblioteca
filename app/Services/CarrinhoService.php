<?php

namespace App\Services;

use App\Models\CarrinhoItem;
use App\Models\Livro;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;

class CarrinhoService
{
    public function obterItens(User $user): Collection
    {
        return $user->carrinhoItems()
            ->with(['livro.editora', 'livro.autores'])
            ->get();
    }

    public function adicionarLivro(User $user, Livro $livro, int $quantidade = 1): CarrinhoItem
    {
        if ($quantidade < 1) {
            throw new InvalidArgumentException('A quantidade deve ser superior a zero.');
        }

        if ($livro->preco_centimos <= 0) {
            throw new InvalidArgumentException('Este livro não se encontra disponível para venda.');
        }

        $item = $user->carrinhoItems()->where('livro_id', $livro->id)->first();

        if ($item) {
            $item->quantidade += $quantidade;
            $item->aviso_abandono_enviado_em = null;
            $item->save();

            return $item;
        }

        return $user->carrinhoItems()->create([
            'livro_id' => $livro->id,
            'quantidade' => $quantidade,
            'aviso_abandono_enviado_em' => null,
        ]);
    }

    public function atualizarQuantidade(User $user, int $itemId, int $quantidade): ?CarrinhoItem
    {
        if ($quantidade < 0) {
            throw new InvalidArgumentException('A quantidade não pode ser negativa.');
        }

        $item = $user->carrinhoItems()->where('id', $itemId)->firstOrFail();

        if ($quantidade === 0) {
            $item->delete();

            return null;
        }

        $item->quantidade = $quantidade;
        $item->aviso_abandono_enviado_em = null;
        $item->save();

        return $item;
    }

    public function removerItem(User $user, int $itemId): bool
    {
        return (bool) $user->carrinhoItems()->where('id', $itemId)->delete();
    }

    public function limpar(User $user): void
    {
        $user->carrinhoItems()->delete();
    }

    public function removerItensComprados(User $user, array $itensComprados): void
    {
        foreach ($itensComprados as $livroId => $quantidadeComprada) {
            $item = $user->carrinhoItems()->where('livro_id', $livroId)->first();

            if (! $item) {
                continue;
            }

            if ($item->quantidade > (int) $quantidadeComprada) {
                $item->decrement('quantidade', (int) $quantidadeComprada);
                $item->aviso_abandono_enviado_em = null;
                $item->touch();
            } else {
                $item->delete();
            }
        }
    }

    public function calcularTotalCentimos(User $user): int
    {
        return (int) $this->obterItens($user)->sum(function (CarrinhoItem $item) {
            return $item->quantidade * $item->livro->preco_centimos;
        });
    }

    public function contarItens(User $user): int
    {
        return (int) $user->carrinhoItems()->sum('quantidade');
    }
}