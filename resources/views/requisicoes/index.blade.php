<x-app-layout>
    <div data-theme="autumn" class="min-h-screen bg-base-200 text-base-content py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col gap-8">
            <div class="card bg-base-100 shadow-xl border border-base-300 p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-amber-950">
                        @if(auth()->user()->isAdmin())
                            {{ __('Gestão Central de Requisições') }}
                        @else
                            {{ __('As Minhas Requisições') }}
                        @endif
                    </h1>
                    <p class="text-sm opacity-80 mt-1">
                        @if(auth()->user()->isAdmin())
                            {{ __('Acompanhamento global de requisições de livros efetuadas na biblioteca.') }}
                        @else
                            {{ __('Consulte o histórico dos seus livros requisitados e as datas de devolução.') }}
                        @endif
                    </p>
                </div>
                <div>
                    <a href="{{ url('/') }}" class="btn btn-primary btn-sm">
                        {{ __('Ir para a Estante') }}
                    </a>
                </div>
            </div>

            <div class="stats stats-vertical lg:stats-horizontal shadow-xl bg-base-100 border border-base-300 w-full">
                <div class="stat">
                    <div class="stat-figure text-primary text-3xl">⏳</div>
                    <div class="stat-title font-semibold text-xs uppercase tracking-wider text-base-content/70">
                        {{ __('Requisições Ativas') }}
                    </div>
                    <div class="stat-value text-primary font-serif font-black">
                        {{ $totalAtivas }}
                    </div>
                    <div class="stat-desc text-xs opacity-70">
                        {{ __('Livros atualmente em empréstimo') }}
                    </div>
                </div>

                <div class="stat">
                    <div class="stat-figure text-secondary text-3xl">📅</div>
                    <div class="stat-title font-semibold text-xs uppercase tracking-wider text-base-content/70">
                        {{ __('Requisições (Últimos 30 dias)') }}
                    </div>
                    <div class="stat-value text-secondary font-serif font-black">
                        {{ $ultimos30Dias }}
                    </div>
                    <div class="stat-desc text-xs opacity-70">
                        {{ __('Movimento total no último mês') }}
                    </div>
                </div>

                <div class="stat">
                    <div class="stat-figure text-accent text-3xl">✅</div>
                    <div class="stat-title font-semibold text-xs uppercase tracking-wider text-base-content/70">
                        {{ __('Livros Entregues Hoje') }}
                    </div>
                    <div class="stat-value text-accent font-serif font-black">
                        {{ $entreguesHoje }}
                    </div>
                    <div class="stat-desc text-xs opacity-70">
                        {{ __('Devoluções concluídas no dia atual') }}
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow-xl border border-base-300">
                <div class="card-body p-6">
                    <div class="flex items-center justify-between pb-4 border-b border-base-200">
                        <h2 class="text-xl font-bold text-amber-950">
                            {{ __('Registo de Requisições') }}
                        </h2>
                        <span class="badge badge-neutral text-xs font-mono">
                            {{ $requisicoes->total() }} {{ __('registos') }}
                        </span>
                    </div>

                    <div class="overflow-x-auto mt-4">
                        <table class="table table-zebra w-full text-xs sm:text-sm">
                            <thead>
                                <tr class="bg-base-200 text-base-content/70 uppercase text-[11px] tracking-wider">
                                    <th>{{ __('Nº Registo') }}</th>
                                    <th>{{ __('Livro') }}</th>
                                    @if(auth()->user()->isAdmin())
                                        <th>{{ __('Cidadão Requisitante') }}</th>
                                    @endif
                                    <th>{{ __('Data Requisição') }}</th>
                                    <th>{{ __('Entrega Prevista') }}</th>
                                    <th>{{ __('Estado') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requisicoes as $requisicao)
                                    @php
                                        $capaUrl = $requisicao->livro->imagem_capa ? (Illuminate\Support\Str::startsWith($requisicao->livro->imagem_capa, ['http://', 'https://']) ? $requisicao->livro->imagem_capa : asset($requisicao->livro->imagem_capa)) : '';
                                    @endphp
                                    <tr class="hover">
                                        <td class="font-mono font-bold text-primary">
                                            #{{ str_pad($requisicao->numero_sequencial, 4, '0', STR_PAD_LEFT) }}
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-3">
                                                @if($capaUrl)
                                                    <img src="{{ $capaUrl }}" alt="{{ $requisicao->livro->nome }}" class="w-10 h-14 object-cover rounded shadow border border-base-300 shrink-0">
                                                @else
                                                    <div class="w-10 h-14 bg-base-300 rounded flex items-center justify-center text-xs">📖</div>
                                                @endif
                                                <div class="flex flex-col">
                                                    <span class="font-bold text-base-content">{{ $requisicao->livro->nome }}</span>
                                                    <span class="text-xs text-base-content/70">ISBN: {{ $requisicao->livro->isbn }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        @if(auth()->user()->isAdmin())
                                            <td>
                                                <div class="flex items-center gap-3">
                                                    <img src="{{ $requisicao->foto_cidadao ?? optional($requisicao->user)->profile_photo_url }}" alt="{{ optional($requisicao->user)->name }}" class="w-9 h-9 rounded-full object-cover border border-primary shrink-0">
                                                    <div class="flex flex-col">
                                                        <span class="font-bold text-base-content">{{ optional($requisicao->user)->name }}</span>
                                                        <span class="text-xs text-base-content/70">{{ optional($requisicao->user)->email }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                        @endif
                                        <td class="font-mono">
                                            {{ $requisicao->data_requisicao->format('d/m/Y') }}
                                        </td>
                                        <td class="font-mono">
                                            {{ $requisicao->data_fim_prevista->format('d/m/Y') }}
                                        </td>
                                        <td>
                                            @if($requisicao->status === 'ativa')
                                                <span class="badge badge-warning text-stone-900 font-bold badge-sm">
                                                    {{ __('Ativa') }}
                                                </span>
                                            @else
                                                <span class="badge badge-success text-white font-bold badge-sm">
                                                    {{ __('Entregue') }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ auth()->user()->isAdmin() ? 6 : 5 }}" class="text-center py-8 text-base-content/60">
                                            {{ __('Não existem requisições registadas até ao momento.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 flex justify-center">
                        {{ $requisicoes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>