<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-base-content leading-tight">
            {{ __('Estado da Encomenda') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="w-full bg-base-100 p-6 rounded-2xl shadow-sm border border-base-300">
                <div class="flex items-center justify-between max-w-2xl mx-auto">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary/20 text-primary flex items-center justify-center font-bold text-sm">
                            ✓
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-wider font-semibold text-primary/80">{{ __('Passo 1') }}</div>
                            <div class="text-sm font-semibold text-base-content">{{ __('Carrinho') }}</div>
                        </div>
                    </div>

                    <div class="h-0.5 flex-1 bg-primary mx-4 sm:mx-6"></div>

                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary/20 text-primary flex items-center justify-center font-bold text-sm">
                            ✓
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-wider font-semibold text-primary/80">{{ __('Passo 2') }}</div>
                            <div class="text-sm font-semibold text-base-content">{{ __('Morada de Entrega') }}</div>
                        </div>
                    </div>

                    <div class="h-0.5 flex-1 {{ $encomenda->estado_pagamento === 'pago' ? 'bg-primary' : 'bg-base-300' }} mx-4 sm:mx-6"></div>

                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full {{ $encomenda->estado_pagamento === 'pago' ? 'bg-primary text-primary-content shadow' : 'bg-warning text-stone-900 font-bold' }} flex items-center justify-center font-bold text-sm">
                            {{ $encomenda->estado_pagamento === 'pago' ? '✓' : '3' }}
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-wider font-bold {{ $encomenda->estado_pagamento === 'pago' ? 'text-primary' : 'text-warning' }}">
                                {{ __('Passo 3') }}
                            </div>
                            <div class="text-sm font-bold text-base-content">
                                {{ $encomenda->estado_pagamento === 'pago' ? __('Pagamento Confirmado') : __('A Processar') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($encomenda->estado_pagamento === 'pago')
                <div class="w-full card bg-base-100 shadow-sm border border-base-300 rounded-2xl p-6 sm:p-8 text-center space-y-3">
                    <div class="w-16 h-16 rounded-full bg-success/20 text-success mx-auto flex items-center justify-center text-3xl shadow-sm">
                        ✓
                    </div>
                    <h3 class="text-2xl font-bold text-base-content">
                        {{ __('Encomenda Confirmada com Sucesso!') }}
                    </h3>
                    <p class="text-sm text-base-content/70 max-w-lg mx-auto">
                        {{ __('O pagamento foi processado e validado com sucesso. A sua encomenda foi registada e os artigos serão expedidos para a morada indicada.') }}
                    </p>
                    <div class="pt-2">
                        <span class="badge badge-success text-white font-bold px-3 py-3 text-xs">
                            {{ __('Pagamento Concluído') }}
                        </span>
                    </div>
                </div>
            @else
                <div class="w-full card bg-base-100 shadow-sm border border-warning/40 rounded-2xl p-6 sm:p-8 text-center space-y-3">
                    <div class="w-16 h-16 rounded-full bg-warning/20 text-warning mx-auto flex items-center justify-center text-3xl shadow-sm">
                        ⏳
                    </div>
                    <h3 class="text-2xl font-bold text-base-content">
                        {{ __('A Processar Confirmação do Pagamento') }}
                    </h3>
                    <p class="text-sm text-base-content/70 max-w-lg mx-auto">
                        {{ __('Recebemos a sua comunicação de pagamento e estamos a aguardar a confirmação segura por parte do Stripe. Esta verificação pode demorar alguns instantes.') }}
                    </p>
                    <div class="pt-2 flex justify-center gap-2">
                        <span class="badge badge-warning text-stone-900 font-bold px-3 py-3 text-xs">
                            {{ __('Aguardando Confirmação') }}
                        </span>
                        <a href="{{ route('checkout.sucesso', $encomenda) }}" class="btn btn-outline btn-xs font-bold">
                            {{ __('Atualizar Estado') }}
                        </a>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="card bg-base-100 shadow-sm border border-base-300 rounded-2xl p-6 space-y-3">
                    <h4 class="font-bold text-base text-base-content border-b border-base-200 pb-2">
                        {{ __('Dados da Encomenda') }}
                    </h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-base-content/70">{{ __('Número') }}:</span>
                            <span class="font-mono font-bold text-primary">{{ $encomenda->numero }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/70">{{ __('Data') }}:</span>
                            <span class="font-mono">{{ $encomenda->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/70">{{ __('Estado Logístico') }}:</span>
                            <span class="badge badge-neutral badge-sm uppercase text-[10px] font-bold">{{ $encomenda->estado }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/70">{{ __('Estado Financeiro') }}:</span>
                            <span class="badge {{ $encomenda->estado_pagamento === 'pago' ? 'badge-success text-white' : 'badge-warning text-stone-900' }} badge-sm font-bold text-[10px]">
                                {{ $encomenda->estado_pagamento }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card bg-base-100 shadow-sm border border-base-300 rounded-2xl p-6 space-y-3">
                    <h4 class="font-bold text-base text-base-content border-b border-base-200 pb-2">
                        {{ __('Morada de Envio') }}
                    </h4>
                    <div class="space-y-1 text-sm text-base-content/80">
                        <div class="font-bold text-base-content">{{ $encomenda->nome_destinatario }}</div>
                        <div>{{ $encomenda->morada }}</div>
                        <div>{{ $encomenda->codigo_postal }} {{ $encomenda->cidade }}</div>
                        <div>{{ $encomenda->pais }}</div>
                        @if ($encomenda->telefone)
                            <div class="text-xs text-base-content/60 pt-1">
                                {{ __('Contacto') }}: {{ $encomenda->telefone }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="w-full card bg-base-100 shadow-sm border border-base-300 rounded-2xl overflow-hidden">
                <div class="p-5 bg-base-200/50 border-b border-base-300">
                    <h4 class="font-bold text-base text-base-content">
                        {{ __('Resumo dos Livros Adquiridos') }}
                    </h4>
                </div>

                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr class="bg-base-200/80 text-base-content/80 text-xs uppercase font-bold">
                                <th class="py-3">{{ __('Livro') }}</th>
                                <th class="text-center py-3">{{ __('Preço Unitário') }}</th>
                                <th class="text-center py-3">{{ __('Quantidade') }}</th>
                                <th class="text-right py-3">{{ __('Subtotal') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-base-200">
                            @foreach ($encomenda->items as $item)
                                <tr>
                                    <td class="py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-14 rounded shadow overflow-hidden bg-base-200 shrink-0">
                                                @if ($item->livro->imagem_capa)
                                                    @php
                                                        $caparaw = $item->livro->imagem_capa;
                                                        if (\illuminate\support\str::startswith($caparaw, ['http://', 'https://'])) {
                                                            $capaurl = $caparaw;
                                                        } elseif (file_exists(public_path('storage/' . $caparaw))) {
                                                            $capaurl = asset('storage/' . $caparaw);
                                                        } else {
                                                            $capaurl = asset($caparaw);
                                                        }
                                                    @endphp
                                                    <img src="{{ $capaurl }}" alt="{{ $item->livro->nome }}" class="object-cover w-full h-full" />
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center text-[8px] text-base-content/40">
                                                        📖
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="font-bold text-sm text-base-content">{{ $item->livro->nome }}</div>
                                                <div class="text-xs text-base-content/60">{{ $item->livro->autores->pluck('nome')->implode(', ') }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center font-mono text-sm">
                                        {{ $item->preco_unitario_formatado }}
                                    </td>
                                    <td class="text-center font-bold text-sm">
                                        {{ $item->quantidade }}
                                    </td>
                                    <td class="text-right font-mono font-bold text-sm text-base-content">
                                        {{ $item->subtotal_formatado }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-base-200/80 border-t-2 border-base-300 font-bold text-base-content">
                            <tr>
                                <td colspan="3" class="text-right py-4 font-black uppercase text-xs tracking-wider">
                                    {{ __('Total da Encomenda') }}:
                                </td>
                                <td class="text-right py-4 text-primary font-mono text-xl font-black">
                                    {{ $encomenda->total_formatado }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row justify-center gap-3 pt-2">
                <a href="{{ url('/') }}" class="btn btn-primary font-bold shadow-md">
                    {{ __('Voltar ao Acervo de Livros') }}
                </a>
                <a href="{{ route('requisicoes.index') }}" class="btn btn-ghost font-medium">
                    {{ __('Consultar Minhas Requisições') }}
                </a>
            </div>

        </div>
    </div>
</x-app-layout>