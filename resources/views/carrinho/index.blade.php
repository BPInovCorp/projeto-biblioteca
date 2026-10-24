<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-base-content leading-tight">
            {{ __('Carrinho de Compras') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="w-full bg-base-100 p-6 rounded-2xl shadow-sm border border-base-300">
                <div class="flex items-center justify-between max-w-2xl mx-auto">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary text-primary-content flex items-center justify-center font-bold text-sm shadow">
                            1
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-wider font-bold text-primary">{{ __('Passo 1') }}</div>
                            <div class="text-sm font-bold text-base-content">{{ __('Carrinho') }}</div>
                        </div>
                    </div>

                    <div class="h-0.5 flex-1 bg-primary/40 mx-4 sm:mx-6"></div>

                    <div class="flex items-center gap-3 opacity-50">
                        <div class="w-10 h-10 rounded-full bg-base-300 text-base-content flex items-center justify-center font-bold text-sm">
                            2
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-wider font-semibold text-base-content/60">{{ __('Passo 2') }}</div>
                            <div class="text-sm font-medium text-base-content">{{ __('Morada de Entrega') }}</div>
                        </div>
                    </div>

                    <div class="h-0.5 flex-1 bg-base-300 mx-4 sm:mx-6"></div>

                    <div class="flex items-center gap-3 opacity-50">
                        <div class="w-10 h-10 rounded-full bg-base-300 text-base-content flex items-center justify-center font-bold text-sm">
                            3
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-wider font-semibold text-base-content/60">{{ __('Passo 3') }}</div>
                            <div class="text-sm font-medium text-base-content">{{ __('Pagamento') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($errors->has('carrinho'))
                <div class="w-full alert alert-error shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ $errors->first('carrinho') }}</span>
                </div>
            @endif

            @if ($itens->isEmpty())
                <div class="w-full card bg-base-100 shadow-sm border border-base-300 text-center py-16 px-4 rounded-2xl">
                    <div class="card-body items-center space-y-4">
                        <div class="w-20 h-20 rounded-full bg-base-200 flex items-center justify-center text-base-content/40">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-base-content">{{ __('O seu carrinho está vazio') }}</h3>
                        <p class="text-base-content/70 max-w-md">
                            {{ __('Ainda não adicionou nenhum livro ao seu carrinho de compras. Explore o acervo da nossa biblioteca e descubra novas obras.') }}
                        </p>
                        <div class="card-actions pt-4">
                            <a href="{{ url('/') }}" class="btn btn-primary">
                                {{ __('Explorar Livros') }}
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="w-full card bg-base-100 shadow-sm border border-base-300 rounded-2xl overflow-hidden">
                    <div class="p-5 bg-base-200/50 border-b border-base-300 flex justify-between items-center">
                        <h3 class="font-bold text-base text-base-content">
                            {{ __('Artigos no Carrinho') }} ({{ $totalItens }})
                        </h3>
                        <button type="button" onclick="document.getElementById('modal-confirmar-limpar-carrinho').showModal()" class="btn btn-ghost btn-xs text-error hover:bg-error/10 font-bold inline-flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            {{ __('Limpar Carrinho') }}
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <thead>
                                <tr class="bg-base-200/80 text-base-content/80 text-xs uppercase font-bold">
                                    <th class="py-3.5">{{ __('Livro') }}</th>
                                    <th class="text-center py-3.5">{{ __('Preço') }}</th>
                                    <th class="text-center py-3.5">{{ __('Quantidade') }}</th>
                                    <th class="text-right py-3.5">{{ __('Subtotal') }}</th>
                                    <th class="text-center py-3.5">{{ __('Ação') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-base-200">
                                @foreach ($itens as $item)
                                    <tr class="hover">
                                        <td class="py-4">
                                            <div class="flex items-center gap-4">
                                                <div class="avatar shrink-0">
                                                    <div class="w-14 h-20 rounded-md shadow border border-base-300 overflow-hidden bg-base-200">
                                                        @if ($item->livro->imagem_capa)
                                                            <img src="{{ $item->livro->imagem_capa }}" alt="{{ $item->livro->nome }}" class="object-cover w-full h-full" />
                                                        @else
                                                            <div class="w-full h-full flex items-center justify-center text-[10px] text-base-content/40 text-center p-1">
                                                                {{ __('Sem capa') }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="space-y-1">
                                                    <div class="font-bold text-base text-base-content leading-snug">
                                                        {{ $item->livro->nome }}
                                                    </div>
                                                    <div class="text-xs text-base-content/70">
                                                        {{ $item->livro->autores->pluck('nome')->implode(', ') }}
                                                    </div>
                                                    @if ($item->livro->editora)
                                                        <div class="text-xs text-base-content/50 font-mono">
                                                            {{ $item->livro->editora->nome }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center font-mono font-semibold whitespace-nowrap">
                                            {{ number_format($item->livro->preco_centimos / 100, 2, ',', '.') }} €
                                        </td>
                                        <td class="text-center whitespace-nowrap">
                                            <form method="POST" action="{{ route('carrinho.atualizar', $item) }}" class="inline-flex items-center justify-center gap-1.5">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" name="quantidade" value="{{ max(0, $item->quantidade - 1) }}" class="btn btn-xs btn-outline btn-square" aria-label="{{ __('Diminuir quantidade') }}">
                                                    -
                                                </button>
                                                <span class="w-8 text-center font-bold text-sm">{{ $item->quantidade }}</span>
                                                <button type="submit" name="quantidade" value="{{ $item->quantidade + 1 }}" class="btn btn-xs btn-outline btn-square" aria-label="{{ __('Aumentar quantidade') }}">
                                                    +
                                                </button>
                                            </form>
                                        </td>
                                        <td class="text-right whitespace-nowrap">
                                            <div class="font-bold text-base text-base-content font-mono">
                                                {{ number_format($item->subtotal_centimos / 100, 2, ',', '.') }} €
                                            </div>
                                            <div class="text-[11px] text-base-content/60 font-mono">
                                                {{ number_format($item->livro->preco_centimos / 100, 2, ',', '.') }} € × {{ $item->quantidade }}
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <form method="POST" action="{{ route('carrinho.remover', $item) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-ghost btn-xs text-error" title="{{ __('Remover do carrinho') }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-base-200/80 border-t-2 border-base-300 font-bold text-base-content text-sm">
                                <tr>
                                    <td class="py-4 font-black uppercase text-xs tracking-wider text-base-content/80">
                                        {{ __('Totais') }}
                                    </td>
                                    <td class="text-center py-4 text-base-content/40 font-mono">
                                        —
                                    </td>
                                    <td class="text-center py-4 text-primary font-bold">
                                        {{ $totalItens }}
                                    </td>
                                    <td class="text-right py-4 font-bold text-primary text-base font-mono">
                                        {{ number_format($totalCentimos / 100, 2, ',', '.') }} €
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="w-full card bg-base-100 shadow-sm border border-base-300 rounded-2xl p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                        <div class="space-y-3">
                            <h3 class="text-lg font-bold text-base-content border-b border-base-200 pb-2">
                                {{ __('Resumo da Encomenda') }}
                            </h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between text-base-content/80">
                                    <span>{{ __('Artigos') }} ({{ $totalItens }})</span>
                                    <span class="font-mono font-medium">{{ number_format($totalCentimos / 100, 2, ',', '.') }} €</span>
                                </div>
                                <div class="flex justify-between items-center text-base-content/80">
                                    <span>{{ __('Portes de Envio') }}</span>
                                    <span class="badge badge-success badge-sm text-white font-bold">{{ __('Gratuito') }}</span>
                                </div>
                            </div>
                            <div class="pt-2 flex justify-between items-baseline border-t border-base-200">
                                <span class="text-base font-bold text-base-content">{{ __('Total a Pagar') }}</span>
                                <span class="text-2xl font-black text-primary font-mono">
                                    {{ number_format($totalCentimos / 100, 2, ',', '.') }} €
                                </span>
                            </div>
                        </div>

                        <div class="flex flex-col gap-3 justify-center">
                            @if (Route::has('checkout.morada'))
                                <a href="{{ route('checkout.morada') }}" class="btn btn-primary btn-block shadow-md font-bold text-sm">
                                    {{ __('Prosseguir para Envio') }}
                                </a>
                            @else
                                <button class="btn btn-primary btn-block font-bold text-sm opacity-60 cursor-not-allowed" disabled>
                                    {{ __('Prosseguir para Envio') }}
                                </button>
                            @endif

                            <div class="flex items-center justify-between pt-1">
                                <a href="{{ url('/') }}" class="btn btn-ghost btn-sm text-xs inline-flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                    {{ __('Continuar a Comprar') }}
                                </a>
                                <span class="text-[11px] text-base-content/50">
                                    {{ __('Ambiente seguro Stripe Sandbox') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>

    <dialog id="modal-confirmar-limpar-carrinho" class="modal">
        <div class="modal-box bg-base-100 border border-base-300 shadow-2xl max-w-md text-center p-6 flex flex-col items-center gap-3">
            <div class="text-4xl text-error">🗑️</div>
            <h3 class="font-bold text-lg text-amber-950">{{ __('Esvaziar Carrinho') }}</h3>
            <p class="text-xs text-base-content/80 leading-relaxed">
                {{ __('Tem a certeza de que pretende remover todos os livros do seu carrinho de compras? Esta ação não pode ser revertida.') }}
            </p>
            <div class="modal-action flex justify-center gap-3 mt-4 w-full">
                <form method="dialog">
                    <button class="btn btn-ghost btn-sm">{{ __('Cancelar') }}</button>
                </form>
                <form method="POST" action="{{ route('carrinho.limpar') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-error btn-sm text-white font-bold">
                        {{ __('Sim, Esvaziar Tudo') }}
                    </button>
                </form>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>{{ __('Fechar') }}</button>
        </form>
    </dialog>
</x-app-layout>