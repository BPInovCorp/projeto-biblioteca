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
                                    <th>{{ __('Ações') }}</th>
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
                                            @php
                                                $cidadaoHistorico = optional($requisicao->user)->requisicoes ? $requisicao->user->requisicoes->map(fn($r) => [
                                                    'numero' => str_pad($r->numero_sequencial, 4, '0', STR_PAD_LEFT),
                                                    'livro' => optional($r->livro)->nome ?? 'Obra',
                                                    'inicio' => $r->data_requisicao->format('d/m/Y'),
                                                    'fim' => $r->data_devolucao ? $r->data_devolucao->format('d/m/Y') : $r->data_fim_prevista->format('d/m/Y'),
                                                    'status' => $r->status,
                                                ])->values() : [];
                                            @endphp
                                            <td>
                                                <button type="button" class="btn btn-ghost btn-xs p-1 flex items-center gap-2.5 text-left normal-case h-auto" onclick="abrirModalCidadao(this)" data-nome="{{ optional($requisicao->user)->name }}" data-email="{{ optional($requisicao->user)->email }}" data-foto="{{ $requisicao->foto_cidadao ?? optional($requisicao->user)->profile_photo_url }}" data-historico="{{ json_encode($cidadaoHistorico) }}" title="{{ __('Ver Detalhes e Histórico') }}">
                                                    <img src="{{ $requisicao->foto_cidadao ?? optional($requisicao->user)->profile_photo_url }}" alt="{{ optional($requisicao->user)->name }}" class="w-8 h-8 rounded-full object-cover border border-primary shrink-0">
                                                    <div class="flex flex-col">
                                                        <span class="font-bold text-base-content hover:underline leading-tight">{{ optional($requisicao->user)->name }}</span>
                                                        <span class="text-[11px] text-base-content/70 leading-tight">{{ optional($requisicao->user)->email }}</span>
                                                    </div>
                                                </button>
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
                                            @elseif($requisicao->status === 'entregue')
                                                <div class="flex flex-col gap-0.5">
                                                    <span class="badge badge-success text-white font-bold badge-sm">
                                                        {{ __('Entregue') }}
                                                    </span>
                                                    @if($requisicao->data_devolucao)
                                                        <span class="text-[10px] font-mono text-base-content/70">
                                                            {{ $requisicao->data_devolucao->format('d/m/Y') }} ({{ $requisicao->dias_decorridos }} {{ $requisicao->dias_decorridos == 1 ? __('dia') : __('dias') }})
                                                        </span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="badge badge-ghost font-bold badge-sm text-base-content/70">
                                                    {{ __('Cancelada') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-1.5">
                                                @if(auth()->user()->isAdmin() && $requisicao->status === 'ativa')
                                                    <button type="button" class="btn btn-success btn-xs text-white font-bold" onclick="abrirModalDevolucao(this)" data-url="{{ route('requisicoes.devolver', $requisicao) }}" data-titulo="{{ $requisicao->livro->nome }}" data-numero="{{ str_pad($requisicao->numero_sequencial, 4, '0', STR_PAD_LEFT) }}" data-cidadao="{{ optional($requisicao->user)->name }}">
                                                        {{ __('Confirmar Receção') }}
                                                    </button>
                                                @endif

                                                @if($requisicao->status === 'ativa' && (auth()->user()->isAdmin() || auth()->id() === $requisicao->user_id))
                                                    <button type="button" class="btn btn-ghost btn-xs text-error font-bold" onclick="abrirModalCancelamento(this)" data-url="{{ route('requisicoes.cancelar', $requisicao) }}" data-numero="{{ str_pad($requisicao->numero_sequencial, 4, '0', STR_PAD_LEFT) }}" data-titulo="{{ $requisicao->livro->nome }}">
                                                        {{ __('Cancelar') }}
                                                    </button>
                                                @endif

                                                @if($requisicao->status === 'entregue')
                                                    @if($requisicao->comentario)
                                                        @if($requisicao->comentario->status === 'suspenso')
                                                            <span class="badge badge-warning text-stone-900 font-bold badge-sm whitespace-nowrap py-1 px-2.5 h-auto leading-tight text-[11px] shadow-sm">
                                                                ⏳ {{ __('avaliação pendente') }}
                                                            </span>
                                                        @elseif($requisicao->comentario->status === 'ativo')
                                                            <span class="badge badge-success text-white font-bold badge-sm whitespace-nowrap py-1 px-2.5 h-auto leading-tight text-[11px] shadow-sm">
                                                                ⭐ {{ $requisicao->comentario->classificacao }}/5 ({{ __('aprovada') }})
                                                            </span>
                                                        @else
                                                            <span class="badge badge-error text-white font-bold badge-sm whitespace-nowrap py-1 px-2.5 h-auto leading-tight text-[11px] shadow-sm">
                                                                ✕ {{ __('recusada') }}
                                                            </span>
                                                        @endif
                                                    @elseif(auth()->id() === $requisicao->user_id)
                                                        <button type="button" class="btn btn-warning btn-xs text-stone-900 font-bold shadow-sm" onclick="abrirModalCriarReview(this)" data-requisicao-id="{{ $requisicao->id }}" data-livro-titulo="{{ $requisicao->livro->nome }}">
                                                            ⭐ {{ __('avaliar livro') }}
                                                        </button>
                                                    @else
                                                        <span class="text-xs text-base-content/50">—</span>
                                                    @endif
                                                @elseif($requisicao->status === 'cancelada')
                                                    <span class="text-xs text-base-content/50">—</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ auth()->user()->isAdmin() ? 7 : 6 }}" class="text-center py-8 text-base-content/60">
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

    <dialog id="modal-confirmar-cancelamento" class="modal">
        <div class="modal-box max-w-md bg-base-100 border border-base-300 shadow-2xl p-6 text-center">
            <div class="text-4xl text-warning mb-2">⚠️</div>
            <h3 class="font-bold text-lg text-amber-950">
                {{ __('Cancelar Requisição') }}
            </h3>
            <p class="text-xs text-base-content/70 mt-1">
                {{ __('Tem a certeza de que pretende cancelar esta requisição? O livro ficará imediatamente disponível para outros utilizadores.') }}
            </p>

            <div class="bg-base-200/60 p-3 rounded-lg border border-base-300 flex flex-col gap-1.5 text-xs text-left mt-4">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-base-content/70">{{ __('Nº Registo') }}:</span>
                    <span id="cancelar-numero-sequencial" class="font-mono font-bold text-primary"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="font-bold text-base-content/70">{{ __('Obra') }}:</span>
                    <span id="cancelar-livro-titulo" class="font-bold text-base-content"></span>
                </div>
            </div>

            <div class="modal-action flex justify-center gap-3 mt-6">
                <button type="button" onclick="document.getElementById('modal-confirmar-cancelamento').close()" class="btn btn-ghost btn-sm">
                    {{ __('Voltar') }}
                </button>
                <form id="form-confirmar-cancelamento" method="POST" action="" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-error btn-sm text-white font-bold">
                        {{ __('Confirmar Cancelamento') }}
                    </button>
                </form>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>{{ __('Fechar') }}</button>
        </form>
    </dialog>

    @if(auth()->user()->isAdmin())
        <dialog id="modal-confirmar-devolucao" class="modal">
            <div class="modal-box max-w-md bg-base-100 border border-base-300 shadow-2xl p-6">
                <form method="dialog">
                    <button class="btn btn-sm btn-circle btn-ghost absolute right-3 top-3">✕</button>
                </form>

                <h3 class="font-bold text-lg text-amber-950 border-b border-base-200 pb-2">
                    {{ __('Confirmar Receção do Livro') }}
                </h3>

                <form id="form-confirmar-devolucao" method="POST" action="" class="mt-4 flex flex-col gap-4">
                    @csrf
                    <div class="bg-base-200/60 p-3 rounded-lg border border-base-300 flex flex-col gap-1.5 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-base-content/70">{{ __('Nº Registo') }}:</span>
                            <span id="devolucao-numero-sequencial" class="font-mono font-bold text-primary"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-base-content/70">{{ __('Obra') }}:</span>
                            <span id="devolucao-livro-titulo" class="font-bold text-base-content"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-base-content/70">{{ __('Cidadão') }}:</span>
                            <span id="devolucao-cidadao-nome" class="text-base-content"></span>
                        </div>
                    </div>

                    <div class="form-control w-full">
                        <label class="label py-1" for="input-data-devolucao">
                            <span class="label-text font-bold text-xs text-base-content">{{ __('Data Efetiva de Entrega') }}</span>
                        </label>
                        <input type="date" name="data_devolucao" id="input-data-devolucao" value="{{ now()->format('Y-m-d') }}" class="input input-bordered input-sm w-full font-mono">
                    </div>

                    <div class="modal-action border-t border-base-200 pt-3 mt-2 flex justify-end gap-2">
                        <button type="button" onclick="document.getElementById('modal-confirmar-devolucao').close()" class="btn btn-ghost btn-sm">
                            {{ __('Cancelar') }}
                        </button>
                        <button type="submit" class="btn btn-success btn-sm text-white font-bold">
                            {{ __('Confirmar Receção') }}
                        </button>
                    </div>
                </form>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button>{{ __('Fechar') }}</button>
            </form>
        </dialog>

        <dialog id="modal-detalhes-cidadao" class="modal">
            <div class="modal-box max-w-2xl bg-base-100 border border-base-300 shadow-2xl p-6">
                <form method="dialog">
                    <button class="btn btn-sm btn-circle btn-ghost absolute right-3 top-3">✕</button>
                </form>

                <h3 class="font-bold text-lg text-amber-950 border-b border-base-200 pb-2 flex items-center gap-2">
                    <span>👤</span> {{ __('Detalhes e Histórico do Cidadão') }}
                </h3>

                <div class="flex items-center gap-4 bg-base-200/60 p-4 rounded-lg border border-base-300 mt-4">
                    <img id="cidadao-modal-foto" src="" alt="Cidadão" class="w-14 h-14 rounded-full object-cover border-2 border-primary shrink-0 shadow">
                    <div class="flex flex-col">
                        <span id="cidadao-modal-nome" class="font-bold text-base text-base-content"></span>
                        <span id="cidadao-modal-email" class="text-xs text-base-content/70 font-mono"></span>
                        <span class="badge badge-outline badge-sm mt-1 w-fit">{{ __('Cidadão Registado') }}</span>
                    </div>
                </div>

                <div class="mt-4">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-base-content/80 mb-2">
                        {{ __('Histórico de Todas as Requisições') }}
                    </h4>
                    <div class="overflow-x-auto max-h-56 overflow-y-auto border border-base-200 rounded-lg">
                        <table class="table table-zebra table-xs w-full">
                            <thead>
                                <tr class="bg-base-200 text-base-content/70">
                                    <th>{{ __('Nº') }}</th>
                                    <th>{{ __('Livro') }}</th>
                                    <th>{{ __('Datas') }}</th>
                                    <th>{{ __('Estado') }}</th>
                                </tr>
                            </thead>
                            <tbody id="cidadao-modal-tabela-corpo"></tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-action border-t border-base-200 pt-3 mt-4 flex justify-end">
                    <button type="button" onclick="document.getElementById('modal-detalhes-cidadao').close()" class="btn btn-ghost btn-sm">
                        {{ __('Fechar') }}
                    </button>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button>{{ __('Fechar') }}</button>
            </form>
        </dialog>
    @endif

    <dialog id="modal-criar-review" class="modal">
        <div class="modal-box max-w-lg bg-base-100 border border-base-300 shadow-2xl p-6">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-3 top-3">✕</button>
            </form>

            <h3 class="font-bold text-lg text-amber-950 border-b border-base-200 pb-2 flex items-center gap-2">
                <span>⭐</span> {{ __('Avaliar Obra Requisitada') }}
            </h3>

            <p class="text-xs text-base-content/70 mt-2">
                {{ __('Partilhe a sua opinião crítica sobre a obra lida. A avaliação ficará visível publicamente após validação.') }}
            </p>

            <div class="bg-base-200/60 p-3 rounded-lg border border-base-300 mt-3">
                <span class="text-[11px] font-bold uppercase tracking-wider text-base-content/60">{{ __('Obra') }}</span>
                <p id="review-livro-titulo" class="font-bold text-sm text-base-content leading-tight mt-0.5"></p>
            </div>

            <form id="form-criar-review" method="POST" action="{{ route('comentarios.store') }}" class="mt-4 flex flex-col gap-4">
                @csrf
                <input type="hidden" name="requisicao_id" id="review-requisicao-id" value="">

                <div class="form-control w-full">
                    <label class="label py-1" for="select-classificacao">
                        <span class="label-text font-bold text-xs text-base-content">{{ __('Classificação (1 a 5 estrelas)') }}</span>
                    </label>
                    <select name="classificacao" id="select-classificacao" required class="select select-bordered select-sm w-full font-bold text-xs">
                        <option value="5">⭐⭐⭐⭐⭐ (5 / 5 - Excelente)</option>
                        <option value="4">⭐⭐⭐⭐ (4 / 5 - Muito Bom)</option>
                        <option value="3">⭐⭐⭐ (3 / 5 - Bom)</option>
                        <option value="2">⭐⭐ (2 / 5 - Suficiente)</option>
                        <option value="1">⭐ (1 / 5 - Fraco)</option>
                    </select>
                </div>

                <div class="form-control w-full">
                    <label class="label py-1" for="textarea-comentario">
                        <span class="label-text font-bold text-xs text-base-content">{{ __('O seu Comentário / Crítica Literária') }}</span>
                    </label>
                    <textarea name="comentario" id="textarea-comentario" required minlength="5" maxlength="1000" rows="4" placeholder="{{ __('Escreva aqui a sua opinião sobre a narrativa, personagens ou escrita...') }}" class="textarea textarea-bordered w-full text-xs sm:text-sm leading-relaxed"></textarea>
                </div>

                <div class="modal-action border-t border-base-200 pt-3 mt-2 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-criar-review').close()" class="btn btn-ghost btn-sm">
                        {{ __('Cancelar') }}
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm font-bold shadow-md">
                        {{ __('Submeter Avaliação') }}
                    </button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>{{ __('Fechar') }}</button>
        </form>
    </dialog>

    <script>
        window.abrirModalCancelamento = function(botao) {
            const form = document.getElementById('form-confirmar-cancelamento');
            if (form) form.action = botao.dataset.url;
            const seq = document.getElementById('cancelar-numero-sequencial');
            if (seq) seq.textContent = '#' + (botao.dataset.numero || '');
            const tit = document.getElementById('cancelar-livro-titulo');
            if (tit) tit.textContent = botao.dataset.titulo || '';
            const modal = document.getElementById('modal-confirmar-cancelamento');
            if (modal) modal.showModal();
        };

        window.abrirModalDevolucao = function(botao) {
            const form = document.getElementById('form-confirmar-devolucao');
            if (form) form.action = botao.dataset.url;
            const seq = document.getElementById('devolucao-numero-sequencial');
            if (seq) seq.textContent = '#' + (botao.dataset.numero || '');
            const tit = document.getElementById('devolucao-livro-titulo');
            if (tit) tit.textContent = botao.dataset.titulo || '';
            const cid = document.getElementById('devolucao-cidadao-nome');
            if (cid) cid.textContent = botao.dataset.cidadao || '';
            const modal = document.getElementById('modal-confirmar-devolucao');
            if (modal) modal.showModal();
        };

        window.abrirModalCidadao = function(botao) {
            const nome = document.getElementById('cidadao-modal-nome');
            if (nome) nome.textContent = botao.dataset.nome || '';
            const email = document.getElementById('cidadao-modal-email');
            if (email) email.textContent = botao.dataset.email || '';
            const foto = document.getElementById('cidadao-modal-foto');
            if (foto) foto.src = botao.dataset.foto || '';

            const historico = JSON.parse(botao.dataset.historico || '[]');
            const tbody = document.getElementById('cidadao-modal-tabela-corpo');
            if (tbody) {
                tbody.innerHTML = '';
                if (historico.length === 0) {
                    const trVazio = document.createElement('tr');
                    const tdVazio = document.createElement('td');
                    tdVazio.colSpan = 4;
                    tdVazio.className = 'text-center py-4 opacity-60';
                    tdVazio.textContent = 'Sem registos de requisições.';
                    trVazio.appendChild(tdVazio);
                    tbody.appendChild(trVazio);
                } else {
                    historico.forEach(function(r) {
                        let badgeClass = 'badge-warning text-stone-900';
                        let badgeText = 'Ativa';
                        if (r.status === 'entregue') {
                            badgeClass = 'badge-success text-white';
                            badgeText = 'Entregue';
                        } else if (r.status === 'cancelada') {
                            badgeClass = 'badge-ghost';
                            badgeText = 'Cancelada';
                        }

                        const tr = document.createElement('tr');

                        const tdNumero = document.createElement('td');
                        tdNumero.className = 'font-mono font-bold text-primary';
                        tdNumero.textContent = '#' + r.numero;

                        const tdLivro = document.createElement('td');
                        tdLivro.className = 'font-bold';
                        tdLivro.textContent = r.livro;

                        const tdDatas = document.createElement('td');
                        tdDatas.className = 'font-mono text-[11px]';
                        tdDatas.textContent = r.inicio + ' → ' + r.fim;

                        const tdEstado = document.createElement('td');
                        const badgeSpan = document.createElement('span');
                        badgeSpan.className = 'badge ' + badgeClass + ' badge-xs font-bold';
                        badgeSpan.textContent = badgeText;
                        tdEstado.appendChild(badgeSpan);

                        tr.appendChild(tdNumero);
                        tr.appendChild(tdLivro);
                        tr.appendChild(tdDatas);
                        tr.appendChild(tdEstado);

                        tbody.appendChild(tr);
                    });
                }
            }

            const modal = document.getElementById('modal-detalhes-cidadao');
            if (modal) modal.showModal();
        };

        window.abrirModalCriarReview = function(botao) {
            const reqId = document.getElementById('review-requisicao-id');
            if (reqId) reqId.value = botao.dataset.requisicaoId || botao.dataset.requisicaoid || '';
            const titulo = document.getElementById('review-livro-titulo');
            if (titulo) titulo.textContent = botao.dataset.livroTitulo || botao.dataset.livrotitulo || '';
            const modal = document.getElementById('modal-criar-review');
            if (modal) modal.showModal();
        };
    </script>
</x-app-layout>