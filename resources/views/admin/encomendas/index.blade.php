<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-base-content leading-tight">
                    {{ __('Gestão de Encomendas') }}
                </h2>
                <p class="text-xs text-base-content/60 mt-0.5">
                    {{ __('Auditoria e monitorização de todas as encomendas da biblioteca.') }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="btn btn-ghost btn-sm">
                    {{ __('Painel de Controlo') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4 bg-base-100 p-4 rounded-2xl shadow-sm border border-base-300">
                <div class="tabs tabs-boxed bg-base-200/80 p-1 rounded-xl">
                    <a href="{{ route('admin.encomendas.index', ['filtro' => 'todas', 'search' => $pesquisa]) }}"
                       class="tab tab-sm font-bold gap-2 {{ $filtro === 'todas' ? 'tab-active !bg-primary !text-primary-content shadow-sm' : '' }}">
                        {{ __('Todas') }}
                        <span class="badge badge-sm {{ $filtro === 'todas' ? 'badge-neutral text-white' : 'badge-ghost' }} font-mono">
                            {{ $totalTodas }}
                        </span>
                    </a>
                    <a href="{{ route('admin.encomendas.index', ['filtro' => 'pagas', 'search' => $pesquisa]) }}"
                       class="tab tab-sm font-bold gap-2 {{ $filtro === 'pagas' ? 'tab-active !bg-primary !text-primary-content shadow-sm' : '' }}">
                        {{ __('Pagas') }}
                        <span class="badge badge-sm {{ $filtro === 'pagas' ? 'badge-neutral text-white' : 'badge-success text-white' }} font-mono">
                            {{ $totalPagas }}
                        </span>
                    </a>
                    <a href="{{ route('admin.encomendas.index', ['filtro' => 'pendentes', 'search' => $pesquisa]) }}"
                       class="tab tab-sm font-bold gap-2 {{ $filtro === 'pendentes' ? 'tab-active !bg-primary !text-primary-content shadow-sm' : '' }}">
                        {{ __('Pendentes') }}
                        <span class="badge badge-sm {{ $filtro === 'pendentes' ? 'badge-neutral text-white' : 'badge-warning text-stone-900' }} font-mono">
                            {{ $totalPendentes }}
                        </span>
                    </a>
                </div>

                <form method="GET" action="{{ route('admin.encomendas.index') }}" class="flex items-center gap-2">
                    <input type="hidden" name="filtro" value="{{ $filtro }}">
                    <div class="relative w-full sm:w-72">
                        <input type="text"
                               name="search"
                               value="{{ $pesquisa }}"
                               placeholder="Pesquisar número, nome ou email..."
                               class="input input-bordered input-sm w-full bg-base-100 text-xs pl-8" />
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute left-2.5 top-1/2 -translate-y-1/2 text-base-content/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0118 0z" />
                        </svg>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm font-bold text-xs">
                        {{ __('Filtrar') }}
                    </button>
                    @if ($pesquisa)
                        <a href="{{ route('admin.encomendas.index', ['filtro' => $filtro]) }}" class="btn btn-ghost btn-sm text-xs">
                            {{ __('Limpar') }}
                        </a>
                    @endif
                </form>
            </div>

            @if ($encomendas->isEmpty())
                <div class="card bg-base-100 shadow-sm border border-base-300 text-center py-16 px-4 rounded-2xl">
                    <div class="card-body items-center space-y-3">
                        <div class="w-16 h-16 rounded-full bg-base-200 flex items-center justify-center text-3xl text-base-content/40">
                            📦
                        </div>
                        <h3 class="text-xl font-bold text-base-content">
                            {{ __('Nenhuma encomenda encontrada') }}
                        </h3>
                        <p class="text-xs text-base-content/60 max-w-md">
                            @if ($pesquisa)
                                {{ __('Não foram encontrados resultados para a pesquisa efetuada. Tente outros termos.') }}
                            @else
                                {{ __('Não existem encomendas registadas no estado selecionado.') }}
                            @endif
                        </p>
                        @if ($pesquisa || $filtro !== 'todas')
                            <div class="card-actions pt-2">
                                <a href="{{ route('admin.encomendas.index') }}" class="btn btn-outline btn-sm font-bold text-xs">
                                    {{ __('Repor Filtros') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="card bg-base-100 shadow-sm border border-base-300 rounded-2xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <thead>
                                <tr class="bg-base-200/80 text-base-content/80 text-xs uppercase font-bold">
                                    <th class="py-3.5">{{ __('Número') }}</th>
                                    <th class="py-3.5">{{ __('Cidadão / Destinatário') }}</th>
                                    <th class="py-3.5">{{ __('Artigos') }}</th>
                                    <th class="text-right py-3.5">{{ __('Total') }}</th>
                                    <th class="text-center py-3.5">{{ __('Pagamento') }}</th>
                                    <th class="py-3.5">{{ __('Data') }}</th>
                                    <th class="text-center py-3.5">{{ __('Ações') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-base-200">
                                @foreach ($encomendas as $encomenda)
                                    <tr class="hover">
                                        <td class="font-mono font-bold text-xs text-primary whitespace-nowrap">
                                            {{ $encomenda->numero }}
                                        </td>
                                        <td>
                                            <div class="font-bold text-sm text-base-content">
                                                {{ $encomenda->nome_destinatario }}
                                            </div>
                                            <div class="text-xs text-base-content/60">
                                                {{ $encomenda->user->email ?? __('Utilizador não associado') }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-xs font-semibold text-base-content">
                                                {{ $encomenda->items->sum('quantidade') }} {{ __('exemplar(es)') }}
                                            </div>
                                            <div class="text-[11px] text-base-content/50 truncate max-w-xs">
                                                {{ $encomenda->items->pluck('livro.nome')->filter()->implode(', ') }}
                                            </div>
                                        </td>
                                        <td class="text-right font-mono font-bold text-sm whitespace-nowrap text-base-content">
                                            {{ $encomenda->total_formatado }}
                                        </td>
                                        <td class="text-center whitespace-nowrap">
                                            @if ($encomenda->estado_pagamento === 'pago')
                                                <span class="badge badge-success text-white font-bold text-[11px] py-2 px-2.5 shadow-sm">
                                                    {{ __('Pago') }}
                                                </span>
                                            @elseif ($encomenda->estado_pagamento === 'pendente')
                                                <span class="badge badge-warning text-stone-900 font-bold text-[11px] py-2 px-2.5 shadow-sm">
                                                    {{ __('Pendente') }}
                                                </span>
                                            @elseif ($encomenda->estado_pagamento === 'falhado')
                                                <span class="badge badge-error text-white font-bold text-[11px] py-2 px-2.5 shadow-sm">
                                                    {{ __('Falhado') }}
                                                </span>
                                            @elseif ($encomenda->estado_pagamento === 'reembolsado')
                                                <span class="badge badge-info text-white font-bold text-[11px] py-2 px-2.5 shadow-sm">
                                                    {{ __('Reembolsado') }}
                                                </span>
                                            @else
                                                <span class="badge badge-ghost text-xs">
                                                    {{ $encomenda->estado_pagamento }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-xs text-base-content/70 whitespace-nowrap font-mono">
                                            {{ $encomenda->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="text-center whitespace-nowrap">
                                            <a href="{{ route('admin.encomendas.show', $encomenda) }}" class="btn btn-outline btn-primary btn-xs font-bold gap-1 shadow-sm">
                                                <span>{{ __('Ver Detalhes') }}</span>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-center pt-2">
                    {{ $encomendas->links() }}
                </div>
            @endif

        </div>
    </div>
</x-app-layout>