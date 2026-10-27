<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-100 p-6 rounded-2xl shadow-sm border border-base-300">
                <div>
                    <h2 class="font-semibold text-2xl text-base-content leading-tight">
                        {{ __('Detalhes da Encomenda') }} <span class="font-mono text-primary">#{{ $encomenda->numero }}</span>
                    </h2>
                    <p class="text-xs text-base-content/60 mt-1">
                        {{ __('Ficha de auditoria comercial e financeira da compra.') }}
                    </p>
                </div>
                <a href="{{ route('admin.encomendas.index') }}" class="btn btn-primary btn-sm font-bold gap-2 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    {{ __('Voltar às Encomendas') }}
                </a>
            </div>

            <div class="w-full bg-base-100 p-6 rounded-2xl shadow-sm border border-base-300">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 items-center">
                    <div>
                        <div class="text-xs uppercase tracking-wider font-bold text-base-content/60">{{ __('Número') }}</div>
                        <div class="text-base sm:text-lg font-black font-mono text-primary mt-0.5">{{ $encomenda->numero }}</div>
                    </div>
                    <div>
                        <div class="text-xs uppercase tracking-wider font-bold text-base-content/60">{{ __('Data de Criação') }}</div>
                        <div class="text-sm font-mono text-base-content mt-0.5">{{ $encomenda->created_at->format('d/m/Y H:i:s') }}</div>
                    </div>
                    <div>
                        <div class="text-xs uppercase tracking-wider font-bold text-base-content/60">{{ __('Estado do Pagamento') }}</div>
                        <div class="mt-1">
                            @if ($encomenda->estado_pagamento === 'pago')
                                <span class="badge badge-success text-white font-bold text-xs py-2 px-3">
                                    {{ __('Pago') }}
                                </span>
                            @elseif ($encomenda->estado_pagamento === 'pendente')
                                <span class="badge badge-warning text-stone-900 font-bold text-xs py-2 px-3">
                                    {{ __('Pendente') }}
                                </span>
                            @elseif ($encomenda->estado_pagamento === 'falhado')
                                <span class="badge badge-error text-white font-bold text-xs py-2 px-3">
                                    {{ __('Falhado') }}
                                </span>
                            @elseif ($encomenda->estado_pagamento === 'reembolsado')
                                <span class="badge badge-info text-white font-bold text-xs py-2 px-3">
                                    {{ __('Reembolsado') }}
                                </span>
                            @else
                                <span class="badge badge-ghost text-xs">{{ $encomenda->estado_pagamento }}</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-xs uppercase tracking-wider font-bold text-base-content/60">{{ __('Estado Logístico') }}</div>
                        <div class="mt-1">
                            <span class="badge badge-neutral text-white font-bold text-xs py-2 px-3 uppercase">
                                {{ $encomenda->estado }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="card bg-base-100 shadow-sm border border-base-300 rounded-2xl p-6 space-y-3">
                    <h3 class="font-bold text-base text-base-content border-b border-base-200 pb-2">
                        {{ __('Identificação do Cidadão') }}
                    </h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-base-content/60">{{ __('Nome') }}:</span>
                            <span class="font-bold text-base-content">{{ $encomenda->user->name ?? __('Não associado') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">{{ __('E-mail') }}:</span>
                            <span class="font-mono text-xs text-base-content/80">{{ $encomenda->user->email ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">{{ __('Conta Criada em') }}:</span>
                            <span class="text-xs text-base-content/70">{{ $encomenda->user ? $encomenda->user->created_at->format('d/m/Y') : '—' }}</span>
                        </div>
                    </div>
                </div>

                <div class="card bg-base-100 shadow-sm border border-base-300 rounded-2xl p-6 space-y-3">
                    <h3 class="font-bold text-base text-base-content border-b border-base-200 pb-2">
                        {{ __('Morada de Entrega') }}
                    </h3>
                    <div class="space-y-1.5 text-sm text-base-content/80">
                        <div class="font-bold text-base-content">{{ $encomenda->nome_destinatario }}</div>
                        <div class="text-xs sm:text-sm">{{ $encomenda->morada }}</div>
                        <div class="text-xs sm:text-sm font-mono">{{ $encomenda->codigo_postal }} {{ $encomenda->cidade }}</div>
                        <div class="text-xs sm:text-sm">{{ $encomenda->pais }}</div>
                        <div class="text-xs text-base-content/60 pt-1">
                            {{ __('Telefone') }}: <span class="font-mono">{{ $encomenda->telefone ?? __('Não facultado') }}</span>
                        </div>
                    </div>
                </div>

                <div class="card bg-base-100 shadow-sm border border-base-300 rounded-2xl p-6 space-y-3">
                    <h3 class="font-bold text-base text-base-content border-b border-base-200 pb-2">
                        {{ __('Auditoria Stripe') }}
                    </h3>
                    <div class="space-y-2.5 text-xs">
                        <div>
                            <span class="block text-base-content/60 font-semibold mb-0.5">{{ __('Stripe Session ID') }}:</span>
                            <span class="block font-mono bg-base-200 p-1.5 rounded text-[10px] break-all select-all">
                                {{ $encomenda->stripe_session_id ?? __('Não disponível') }}
                            </span>
                        </div>
                        <div>
                            <span class="block text-base-content/60 font-semibold mb-0.5">{{ __('Payment Intent ID') }}:</span>
                            <span class="block font-mono bg-base-200 p-1.5 rounded text-[10px] break-all select-all">
                                {{ $encomenda->stripe_payment_intent_id ?? __('Aguardando confirmação') }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center pt-1">
                            <span class="text-base-content/60">{{ __('Ambiente') }}:</span>
                            <span class="badge badge-outline badge-sm text-[10px] font-bold">{{ __('Stripe Sandbox') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full card bg-base-100 shadow-sm border border-base-300 rounded-2xl overflow-hidden">
                <div class="p-5 bg-base-200/50 border-b border-base-300">
                    <h3 class="font-bold text-base text-base-content">
                        {{ __('Artigos da Encomenda') }} ({{ $encomenda->items->sum('quantidade') }} {{ __('unidades') }})
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr class="bg-base-200/80 text-base-content/80 text-xs uppercase font-bold">
                                <th class="py-3.5">{{ __('Livro') }}</th>
                                <th class="py-3.5">{{ __('Editora') }}</th>
                                <th class="text-center py-3.5">{{ __('Preço Unitário (Histórico)') }}</th>
                                <th class="text-center py-3.5">{{ __('Quantidade') }}</th>
                                <th class="text-right py-3.5">{{ __('Subtotal') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-base-200">
                            @foreach ($encomenda->items as $item)
                                <tr class="hover">
                                    <td class="py-4">
                                        <div class="flex items-center gap-4">
                                            <div class="avatar shrink-0">
                                                <div class="w-12 h-16 rounded shadow border border-base-300 overflow-hidden bg-base-200">
                                                    @if ($item->livro && $item->livro->imagem_capa)
                                                        @php
                                                            $capaRaw = $item->livro->imagem_capa;
                                                            if (\Illuminate\Support\Str::startsWith($capaRaw, ['http://', 'https://'])) {
                                                                $capaUrl = $capaRaw;
                                                            } elseif (file_exists(public_path('storage/' . $capaRaw))) {
                                                                $capaUrl = asset('storage/' . $capaRaw);
                                                            } else {
                                                                $capaUrl = asset($capaRaw);
                                                            }
                                                        @endphp
                                                        <img src="{{ $capaUrl }}" alt="{{ $item->livro->nome }}" class="object-cover w-full h-full" />
                                                    @else
                                                        <div class="w-full h-full flex items-center justify-center text-xs text-base-content/40">
                                                            📖
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div>
                                                <div class="font-bold text-sm text-base-content">
                                                    {{ $item->livro->nome ?? __('Obra removida') }}
                                                </div>
                                                <div class="text-xs text-base-content/60">
                                                    {{ optional($item->livro)->autores ? $item->livro->autores->pluck('nome')->implode(', ') : '—' }}
                                                </div>
                                                @if (optional($item->livro)->isbn)
                                                    <div class="text-[10px] font-mono text-base-content/40">
                                                        ISBN: {{ $item->livro->isbn }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-xs text-base-content/70">
                                        {{ optional(optional($item->livro)->editora)->nome ?? '—' }}
                                    </td>
                                    <td class="text-center font-mono text-sm whitespace-nowrap">
                                        {{ $item->preco_unitario_formatado }}
                                    </td>
                                    <td class="text-center font-bold text-sm">
                                        {{ $item->quantidade }}
                                    </td>
                                    <td class="text-right font-mono font-bold text-sm whitespace-nowrap text-base-content">
                                        {{ $item->subtotal_formatado }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-base-200/80 border-t-2 border-base-300 font-bold text-base-content text-sm">
                            <tr>
                                <td colspan="4" class="text-right py-4 font-black uppercase text-xs tracking-wider">
                                    {{ __('Total Liquidado na Encomenda') }}:
                                </td>
                                <td class="text-right py-4 text-primary font-mono text-xl font-black whitespace-nowrap">
                                    {{ $encomenda->total_formatado }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>