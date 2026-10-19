<x-app-layout>
    <div data-theme="autumn" class="min-h-screen bg-base-200 text-base-content py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col gap-6">
            <div class="card bg-base-100 shadow-xl border border-base-300 p-6 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight text-amber-950">
                        {{ __('Moderação de Avaliações') }} ✍️
                    </h1>
                    <p class="text-xs opacity-80 mt-1">
                        {{ __('Faça a triagem, aprovação e recusa justificada de críticas literárias submetidas pelos cidadãos.') }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline btn-sm">
                        {{ __('Voltar ao Painel') }}
                    </a>
                </div>
            </div>

            <div class="card bg-base-100 shadow-xl border border-base-300 p-6">
                <div id="tabs-filtro-status" class="tabs tabs-boxed bg-base-200 p-1 mb-6 flex flex-wrap gap-1">
                    <a href="{{ route('admin.comentarios.index', ['estado' => 'suspenso']) }}" class="tab tab-sm font-bold {{ $estado === 'suspenso' ? 'tab-active !bg-warning !text-stone-900' : '' }}">
                        ⏳ {{ __('Suspensas') }} ({{ $totalSuspensos }})
                    </a>
                    <a href="{{ route('admin.comentarios.index', ['estado' => 'ativo']) }}" class="tab tab-sm font-bold {{ $estado === 'ativo' ? 'tab-active !bg-success !text-white' : '' }}">
                        ✓ {{ __('Ativas') }} ({{ $totalAtivos }})
                    </a>
                    <a href="{{ route('admin.comentarios.index', ['estado' => 'recusado']) }}" class="tab tab-sm font-bold {{ $estado === 'recusado' ? 'tab-active !bg-error !text-white' : '' }}">
                        ✕ {{ __('Recusadas') }} ({{ $totalRecusados }})
                    </a>
                    <a href="{{ route('admin.comentarios.index', ['estado' => 'todos']) }}" class="tab tab-sm font-bold {{ $estado === 'todos' ? 'tab-active !bg-primary !text-white' : '' }}">
                        📚 {{ __('Todas') }} ({{ $totalTodos }})
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table id="tabela-comentarios-moderacao" class="table table-zebra w-full text-xs sm:text-sm">
                        <thead>
                            <tr class="bg-base-200 text-base-content/70 uppercase text-[11px] tracking-wider">
                                <th>{{ __('Livro') }}</th>
                                <th>{{ __('Cidadão') }}</th>
                                <th>{{ __('Classificação') }}</th>
                                <th>{{ __('Comentário / Crítica') }}</th>
                                <th>{{ __('Data') }}</th>
                                <th class="text-center">{{ __('Estado') }}</th>
                                <th class="text-end">{{ __('Ações') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($comentarios as $comentario)
                                @php
                                    $capaUrl = $comentario->livro->imagem_capa ? (Illuminate\Support\Str::startsWith($comentario->livro->imagem_capa, ['http://', 'https://']) ? $comentario->livro->imagem_capa : asset($comentario->livro->imagem_capa)) : '';
                                @endphp
                                <tr class="hover">
                                    <td>
                                        <div class="flex items-center gap-3">
                                            @if($capaUrl)
                                                <img src="{{ $capaUrl }}" alt="{{ $comentario->livro->nome }}" class="w-9 h-12 object-cover rounded shadow border border-base-300 shrink-0">
                                            @else
                                                <div class="w-9 h-12 bg-base-300 rounded flex items-center justify-center text-xs">📖</div>
                                            @endif
                                            <div class="flex flex-col">
                                                <span class="font-bold text-base-content leading-tight">{{ $comentario->livro->nome }}</span>
                                                <span class="text-[11px] text-base-content/70">{{ optional($comentario->livro->editora)->nome }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <img src="{{ optional($comentario->user)->profile_photo_url }}" alt="{{ optional($comentario->user)->name }}" class="w-7 h-7 rounded-full object-cover border border-primary shrink-0">
                                            <div class="flex flex-col">
                                                <span class="font-bold text-base-content leading-tight">{{ optional($comentario->user)->name }}</span>
                                                <span class="text-[11px] text-base-content/70">{{ optional($comentario->user)->email }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="text-xs">{{ str_repeat('⭐', $comentario->classificacao) }}</span>
                                            <span class="text-[10px] font-bold opacity-70">{{ $comentario->classificacao }} / 5</span>
                                        </div>
                                    </td>
                                    <td class="max-w-xs">
                                        <p class="text-xs text-base-content/90 line-clamp-2 leading-relaxed text-justify" title="{{ $comentario->comentario }}">
                                            "{{ $comentario->comentario }}"
                                        </p>
                                    </td>
                                    <td class="font-mono text-xs">
                                        {{ $comentario->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="text-center">
                                        @if($comentario->status === 'suspenso')
                                            <span class="badge badge-warning text-stone-900 font-bold text-xs h-auto py-1 px-3 inline-flex flex-col items-center justify-center leading-tight shadow-sm whitespace-nowrap">
                                                <span>⏳ {{ __('Pendente') }}</span>
                                                <span>{{ __('Avaliação') }}</span>
                                            </span>
                                        @elseif($comentario->status === 'ativo')
                                            <span class="badge badge-success text-white font-bold badge-sm whitespace-nowrap py-1 px-2.5 h-auto text-xs shadow-sm">
                                                ✓ {{ __('Ativo') }}
                                            </span>
                                        @else
                                            <div class="flex flex-col items-center gap-0.5">
                                                <span class="badge badge-error text-white font-bold badge-sm whitespace-nowrap py-1 px-2.5 h-auto text-xs shadow-sm">
                                                    ✕ {{ __('Recusado') }}
                                                </span>
                                                @if($comentario->justificacao)
                                                    <span class="text-[10px] italic text-base-content/70 max-w-xs truncate" title="{{ $comentario->justificacao }}">
                                                        {{ $comentario->justificacao }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($comentario->status === 'suspenso')
                                            <div class="flex justify-end items-center gap-1.5">
                                                <form method="POST" action="{{ route('admin.comentarios.aprovar', $comentario) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-xs text-white font-bold" title="{{ __('Aprovar e publicar no catálogo') }}">
                                                        {{ __('Aprovar') }}
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-error btn-xs text-white font-bold btn-recusar-comentario" onclick="abrirModalRecusarComentario(this)"
                                                    data-url="{{ route('admin.comentarios.recusar', $comentario) }}"
                                                    data-livro="{{ $comentario->livro->nome }}"
                                                    data-cidadao="{{ optional($comentario->user)->name }}"
                                                    data-texto="{{ $comentario->comentario }}"
                                                    title="{{ __('Recusar com justificação') }}">
                                                    {{ __('Recusar') }}
                                                </button>
                                            </div>
                                        @else
                                            <span class="text-xs text-base-content/40">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-8 opacity-60">
                                        {{ __('Não existem avaliações para apresentar.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 flex justify-center">
                    {{ $comentarios->links() }}
                </div>
            </div>
        </div>
    </div>

    <dialog id="modal-recusar-comentario" class="modal">
        <div class="modal-box max-w-md bg-base-100 border border-base-300 shadow-2xl p-6">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-3 top-3">✕</button>
            </form>

            <h3 class="font-bold text-lg text-amber-950 border-b border-base-200 pb-2 flex items-center gap-2">
                <span>✕</span> {{ __('Recusar Avaliação') }}
            </h3>

            <p class="text-xs text-base-content/70 mt-2">
                {{ __('Indique a justificação da recusa. Esta informação será enviada por email ao cidadão.') }}
            </p>

            <div class="bg-base-200/60 p-3 rounded-lg border border-base-300 mt-3 text-xs flex flex-col gap-1">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-base-content/70">{{ __('Obra:') }}</span>
                    <span id="recusar-livro-titulo" class="font-bold text-base-content"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="font-bold text-base-content/70">{{ __('Cidadão:') }}</span>
                    <span id="recusar-cidadao-nome" class="text-base-content"></span>
                </div>
                <div class="mt-1 pt-1 border-t border-base-300">
                    <span class="font-bold text-base-content/70">{{ __('Comentário original:') }}</span>
                    <p id="recusar-comentario-texto" class="italic text-base-content/80 mt-0.5 line-clamp-2"></p>
                </div>
            </div>

            <form id="form-recusar-comentario" method="POST" action="" class="mt-4 flex flex-col gap-3">
                @csrf
                <div class="form-control w-full">
                    <label class="label py-1" for="textarea-justificacao">
                        <span class="label-text font-bold text-xs text-base-content">{{ __('Justificação da Recusa (obrigatório)') }}</span>
                    </label>
                    <textarea name="justificacao" id="textarea-justificacao" required minlength="5" maxlength="500" rows="3" placeholder="{{ __('ex: O comentário contém linguagem inadequada ou não se relaciona com a obra...') }}" class="textarea textarea-bordered w-full text-xs leading-relaxed"></textarea>
                    <span class="text-[10px] text-base-content/60 mt-1">{{ __('A justificação deve ter entre 5 e 500 caracteres.') }}</span>
                </div>

                <div class="modal-action border-t border-base-200 pt-3 mt-2 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-recusar-comentario').close()" class="btn btn-ghost btn-sm">
                        {{ __('Cancelar') }}
                    </button>
                    <button type="submit" class="btn btn-error btn-sm text-white font-bold shadow-md">
                        {{ __('Confirmar Recusa') }}
                    </button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>{{ __('Fechar') }}</button>
        </form>
    </dialog>

    <script>
        window.abrirModalRecusarComentario = function(botao) {
            const form = document.getElementById('form-recusar-comentario');
            if (form) form.action = botao.dataset.url;
            const livroEl = document.getElementById('recusar-livro-titulo');
            if (livroEl) livroEl.textContent = botao.dataset.livro;
            const cidadaoEl = document.getElementById('recusar-cidadao-nome');
            if (cidadaoEl) cidadaoEl.textContent = botao.dataset.cidadao;
            const textoEl = document.getElementById('recusar-comentario-texto');
            if (textoEl) textoEl.textContent = botao.dataset.texto;

            const textarea = document.getElementById('textarea-justificacao');
            if (textarea) textarea.value = '';

            const modal = document.getElementById('modal-recusar-comentario');
            if (modal) modal.showModal();
        };
    </script>
</x-app-layout>