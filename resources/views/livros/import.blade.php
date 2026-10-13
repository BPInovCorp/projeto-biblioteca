<x-app-layout>
    <div data-theme="autumn" class="min-h-screen bg-base-200 text-base-content py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col gap-6">
            <div class="card bg-base-100 shadow-xl border border-base-300 p-6 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight text-amber-950">
                        {{ __('Importar Livros da Google Books') }} 🌐
                    </h1>
                    <p class="text-xs opacity-80 mt-1">
                        {{ __('Pesquise no catálogo global da Google e adicione novas obras diretamente ao acervo da biblioteca.') }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('livros.index') }}" class="btn btn-outline btn-sm">
                        {{ __('Voltar à Gestão de Livros') }}
                    </a>
                </div>
            </div>

            <div class="card bg-base-100 shadow-xl border border-base-300 p-6 max-w-4xl mx-auto w-full">
                <form id="form-pesquisa-google" method="GET" action="{{ route('livros.importar.search') }}" class="flex flex-col gap-2">
                    <label class="label p-0" for="input-query-google">
                        <span class="label-text text-xs font-bold uppercase tracking-wider text-amber-950">
                            {{ __('Pesquisa no Catálogo da Google') }}
                        </span>
                    </label>

                    <div class="join w-full shadow-sm">
                        <select name="tipo" id="select-tipo-pesquisa" class="select select-bordered select-sm join-item bg-base-100 text-base-content font-medium text-xs">
                            <option value="geral" {{ ($tipo ?? 'geral') === 'geral' ? 'selected' : '' }}>{{ __('Pesquisa Geral') }}</option>
                            <option value="titulo" {{ ($tipo ?? '') === 'titulo' ? 'selected' : '' }}>{{ __('Título') }}</option>
                            <option value="autor" {{ ($tipo ?? '') === 'autor' ? 'selected' : '' }}>{{ __('Autor') }}</option>
                            <option value="isbn" {{ ($tipo ?? '') === 'isbn' ? 'selected' : '' }}>{{ __('ISBN') }}</option>
                            <option value="editora" {{ ($tipo ?? '') === 'editora' ? 'selected' : '' }}>{{ __('Editora') }}</option>
                        </select>
                        <input type="text" id="input-query-google" name="query" value="{{ $query ?? '' }}" required placeholder="{{ __('Termo a pesquisar...') }}" class="input input-bordered input-sm join-item grow bg-base-100 text-base-content" />
                        <button type="submit" class="btn btn-primary btn-sm join-item">
                            🔍 {{ __('Pesquisar') }}
                        </button>
                        @if($pesquisado)
                            <a href="{{ route('livros.importar') }}" class="btn btn-neutral btn-outline btn-sm join-item">
                                {{ __('Limpar') }}
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            @if($pesquisado)
                @if(!empty($erro))
                    <div class="card bg-amber-950/10 border-2 border-amber-900/30 p-4 sm:p-5 rounded-2xl shadow-lg flex flex-col sm:flex-row items-center justify-between gap-4 text-amber-950 mb-6">
                        <div class="flex items-center gap-3 w-full sm:w-auto">
                            <span class="text-3xl">⚠️</span>
                            <div>
                                <h4 class="font-extrabold text-sm text-amber-950">{{ __('Instabilidade da Google Books API') }}</h4>
                                <p class="text-xs text-amber-950/80 mt-0.5">{{ $erro }}</p>
                            </div>
                        </div>
                        <a href="{{ request()->fullUrl() }}" class="btn btn-warning btn-sm text-stone-900 font-bold shadow-md shrink-0 w-full sm:w-auto">
                            🔄 {{ __('Tentar Novamente') }}
                        </a>
                    </div>
                @else
                    <div class="flex items-center justify-between px-2">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-amber-950">
                            {{ __('Resultados encontrados para') }} "<span class="italic">{{ $query }}</span>"
                        </h2>
                        <span class="badge badge-neutral text-xs font-mono font-bold">
                            {{ count($items) }} {{ __('apresentados') }} / {{ $total }} {{ __('no catálogo Google') }}
                        </span>
                    </div>

                    @if(count($items) > 0)
                    <div id="grelha-resultados-google" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($items as $item)
                            <div class="card-livro-google card bg-base-100 shadow-xl border border-base-300 flex flex-col justify-between hover:shadow-2xl transition-all duration-300 p-5">
                                <div>
                                    <div class="flex gap-4">
                                        @if(!empty($item['imagem_capa']))
                                            <img src="{{ $item['imagem_capa'] }}" alt="{{ $item['nome'] }}" class="w-24 h-36 object-cover rounded shadow-md border border-base-300 shrink-0">
                                        @else
                                            <div class="w-24 h-36 bg-base-300 rounded flex items-center justify-center text-3xl shrink-0">
                                                📖
                                            </div>
                                        @endif

                                        <div class="flex flex-col justify-between grow overflow-hidden">
                                            <div>
                                                <h3 class="font-bold text-sm text-base-content line-clamp-2 leading-snug" title="{{ $item['nome'] }}">
                                                    {{ $item['nome'] }}
                                                </h3>
                                                <p class="text-xs text-primary font-semibold mt-1 line-clamp-1">
                                                    {{ implode(', ', $item['autores']) }}
                                                </p>
                                                <p class="text-[11px] text-base-content/70 mt-0.5 line-clamp-1">
                                                    {{ $item['editora'] }} @if(!empty($item['ano_publicacao'])) ({{ $item['ano_publicacao'] }}) @endif
                                                </p>
                                            </div>

                                            <div class="flex flex-wrap gap-1 mt-2">
                                                @if(!empty($item['isbn']))
                                                    <span class="badge badge-outline text-[10px] font-mono">
                                                        ISBN: {{ $item['isbn'] }}
                                                    </span>
                                                @else
                                                    <span class="badge badge-ghost text-[10px] font-mono opacity-60">
                                                        {{ __('Sem ISBN') }}
                                                    </span>
                                                @endif

                                                @if(!empty($item['preco']))
                                                    <span class="badge badge-accent badge-sm font-bold text-[10px]">
                                                        {{ number_format((float)$item['preco'], 2, ',', '.') }} {{ $item['moeda'] ?? 'EUR' }}
                                                    </span>
                                                @else
                                                    <span class="badge badge-ghost badge-sm text-[10px] opacity-60">
                                                        {{ __('Preço sob consulta') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <p class="text-xs text-base-content/80 mt-3 line-clamp-4 leading-relaxed text-justify">
                                        {{ $item['bibliografia'] ?? __('Sem sinopse disponível no catálogo da Google.') }}
                                    </p>
                                </div>

                                <div class="flex items-center justify-between mt-4 pt-3 border-t border-base-200 gap-2">
                                    <button type="button" class="btn btn-ghost btn-xs text-primary font-bold btn-previsualizar-google shrink-0" onclick="abrirModalPrevisualizarGoogle(this)"
                                        data-nome="{{ $item['nome'] }}"
                                        data-autores="{{ json_encode($item['autores']) }}"
                                        data-editora="{{ $item['editora'] }}"
                                        data-ano="{{ $item['ano_publicacao'] ?? '' }}"
                                        data-isbn="{{ $item['isbn'] ?? '' }}"
                                        data-preco="{{ !empty($item['preco']) ? number_format((float)$item['preco'], 2, ',', '.') . ' ' . ($item['moeda'] ?? 'EUR') : '' }}"
                                        data-raw-preco="{{ $item['preco'] ?? '' }}"
                                        data-capa="{{ $item['imagem_capa'] ?? '' }}"
                                        data-sinopse="{{ $item['bibliografia'] ?? '' }}"
                                        data-ja-no-acervo="{{ $item['ja_no_acervo'] ? '1' : '0' }}">
                                        👁️ {{ __('Pré-visualizar') }}
                                    </button>

                                    @if($item['ja_no_acervo'])
                                        <span class="badge badge-success text-white font-bold gap-1 text-xs">
                                            ✓ {{ __('Já no Acervo') }}
                                        </span>
                                    @else
                                        <form method="POST" action="{{ route('livros.importar.store') }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="nome" value="{{ $item['nome'] }}">
                                            <input type="hidden" name="editora" value="{{ $item['editora'] }}">
                                            @foreach($item['autores'] as $autor)
                                                <input type="hidden" name="autores[]" value="{{ $autor }}">
                                            @endforeach
                                            <input type="hidden" name="isbn" value="{{ $item['isbn'] ?? '' }}">
                                            <input type="hidden" name="bibliografia" value="{{ $item['bibliografia'] ?? '' }}">
                                            <input type="hidden" name="imagem_capa" value="{{ $item['imagem_capa'] ?? '' }}">
                                            <input type="hidden" name="preco" value="{{ $item['preco'] ?? '' }}">

                                            <button type="submit" class="btn btn-primary btn-sm text-xs shadow-md">
                                                📥 {{ __('Importar') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if(($totalpages ?? $totalpages ?? 0) > 1)
                        <div id="paginacao-google-books" class="flex justify-center mt-8">
                            <div class="join shadow-md border border-base-300 bg-base-100">
                                @if($page > 1)
                                    <a href="{{ route('livros.importar.search', ['query' => $query, 'tipo' => $tipo, 'page' => $page - 1]) }}" class="btn btn-sm btn-outline join-item">
                                        « {{ __('página anterior') }}
                                    </a>
                                @else
                                    <button type="button" disabled class="btn btn-sm btn-disabled join-item opacity-40">
                                        « {{ __('página anterior') }}
                                    </button>
                                @endif

                                <span class="btn btn-sm btn-ghost no-animation join-item font-mono font-bold text-xs pointer-events-none">
                                    {{ __('página :atual de :total', ['atual' => $page ?? 1, 'total' => $totalpages ?? $totalpages ?? 1]) }}
                                </span>

                                @if(($page ?? 1) < ($totalpages ?? $totalpages ?? 1))
                                    <a href="{{ route('livros.importar.search', ['query' => $query, 'tipo' => $tipo, 'page' => $page + 1]) }}" class="btn btn-sm btn-outline join-item">
                                        {{ __('página seguinte') }} »
                                    </a>
                                @else
                                    <button type="button" disabled class="btn btn-sm btn-disabled join-item opacity-40">
                                        {{ __('página seguinte') }} »
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endif
                @else
                    <div class="alert alert-info shadow-lg">
                        <span>ℹ️ {{ __('Nenhum livro encontrado na Google Books para a pesquisa indicada. Tente pesquisar por outro termo, autor ou ISBN.') }}</span>
                    </div>
                @endif
            @endif
            @else
                <div class="card bg-base-100 shadow-md border border-dashed border-base-300 p-12 text-center flex flex-col items-center gap-3">
                    <div class="text-5xl">📚</div>
                    <h3 class="font-bold text-lg text-amber-950">
                        {{ __('Pesquisa no Catálogo Global da Google Books') }}
                    </h3>
                    <p class="text-xs text-base-content/70 max-w-md">
                        {{ __('Introduza um termo na barra superior para consultar obras da literatura mundial e adicioná-las instantaneamente à biblioteca com todos os metadados associados.') }}
                    </p>
                </div>
            @endif
        </div>
    </div>

    <dialog id="modal-previsualizar-livro-google" class="modal">
        <div class="modal-box max-w-2xl bg-base-100 border border-base-300 shadow-2xl p-6">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-3 top-3">✕</button>
            </form>

            <h3 class="font-bold text-lg text-amber-950 border-b border-base-200 pb-2">
                {{ __('Detalhes da Obra (Google Books)') }}
            </h3>

            <div class="flex flex-col sm:flex-row gap-5 mt-4">
                <div class="flex flex-col items-center sm:items-start shrink-0">
                    <img id="prev-google-capa" src="" alt="{{ __('Capa da Obra') }}" class="w-32 h-48 object-cover rounded shadow-md border border-base-300">
                    <div class="flex flex-col gap-1.5 mt-3 w-full">
                        <span id="prev-google-isbn" class="badge badge-outline text-[11px] font-mono w-full justify-center"></span>
                        <span id="prev-google-preco" class="badge badge-accent badge-sm font-bold text-xs w-full justify-center"></span>
                    </div>
                </div>

                <div class="flex flex-col grow justify-between">
                    <div>
                        <h4 id="prev-google-titulo" class="font-bold text-lg text-base-content leading-tight"></h4>
                        <p id="prev-google-autores" class="text-xs text-primary font-semibold mt-1"></p>
                        <p id="prev-google-editora-ano" class="text-xs text-base-content/70 mt-0.5"></p>

                        <div class="mt-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-amber-950 block mb-1">
                                {{ __('Sinopse Completa') }}
                            </span>
                            <div class="bg-base-200/50 p-4 rounded-lg border border-base-300 max-h-56 overflow-y-auto text-xs sm:text-sm text-justify leading-relaxed">
                                <p id="prev-google-sinopse" class="text-base-content/80"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-action border-t border-base-200 pt-3 mt-4 flex items-center justify-between">
                <button type="button" onclick="document.getElementById('modal-previsualizar-livro-google').close()" class="btn btn-ghost btn-sm">
                    {{ __('Fechar') }}
                </button>

                <div id="prev-google-action-container">
                    <span id="prev-google-ja-acervo-badge" class="badge badge-success text-white font-bold gap-1 text-xs hidden">
                        ✓ {{ __('Já no Acervo') }}
                    </span>

                    <form id="prev-google-form-importar" method="POST" action="{{ route('livros.importar.store') }}" class="inline">
                        @csrf
                        <input type="hidden" name="nome" id="prev-input-nome" value="">
                        <input type="hidden" name="editora" id="prev-input-editora" value="">
                        <div id="prev-input-autores-container"></div>
                        <input type="hidden" name="isbn" id="prev-input-isbn" value="">
                        <input type="hidden" name="bibliografia" id="prev-input-bibliografia" value="">
                        <input type="hidden" name="imagem_capa" id="prev-input-imagem-capa" value="">
                        <input type="hidden" name="preco" id="prev-input-preco" value="">

                        <button type="submit" class="btn btn-primary btn-sm text-xs shadow-md">
                            📥 {{ __('Importar para o Acervo') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>{{ __('Fechar') }}</button>
        </form>
    </dialog>

    <script>
        window.abrirModalPrevisualizarGoogle = function(botao) {
            const dados = botao.dataset;

            const tituloEl = document.getElementById('prev-google-titulo');
            if (tituloEl) tituloEl.textContent = dados.nome || 'Título Desconhecido';

            const autoresRaw = JSON.parse(dados.autores || '[]');
            const autoresEl = document.getElementById('prev-google-autores');
            if (autoresEl) autoresEl.textContent = autoresRaw.join(', ') || 'Autor Desconhecido';

            const editoraAnoEl = document.getElementById('prev-google-editora-ano');
            if (editoraAnoEl) {
                const editora = dados.editora || 'Editora Desconhecida';
                const ano = dados.ano ? ' (' + dados.ano + ')' : '';
                editoraAnoEl.textContent = editora + ano;
            }

            const isbnEl = document.getElementById('prev-google-isbn');
            if (isbnEl) {
                if (dados.isbn) {
                    isbnEl.textContent = 'ISBN: ' + dados.isbn;
                    isbnEl.className = 'badge badge-outline text-[11px] font-mono w-full justify-center';
                } else {
                    isbnEl.textContent = 'Sem ISBN';
                    isbnEl.className = 'badge badge-ghost text-[11px] font-mono opacity-60 w-full justify-center';
                }
            }

            const precoEl = document.getElementById('prev-google-preco');
            if (precoEl) {
                if (dados.preco) {
                    precoEl.textContent = dados.preco;
                    precoEl.className = 'badge badge-accent badge-sm font-bold text-xs w-full justify-center';
                } else {
                    precoEl.textContent = 'Preço sob consulta';
                    precoEl.className = 'badge badge-ghost badge-sm text-xs opacity-60 w-full justify-center';
                }
            }

            const sinopseEl = document.getElementById('prev-google-sinopse');
            if (sinopseEl) {
                sinopseEl.textContent = dados.sinopse || 'Sem sinopse disponível no catálogo da Google.';
            }

            const capaEl = document.getElementById('prev-google-capa');
            if (capaEl) {
                if (dados.capa && dados.capa.startsWith('https://')) {
                    capaEl.src = dados.capa;
                    capaEl.classList.remove('hidden');
                } else {
                    capaEl.src = '';
                    capaEl.classList.add('hidden');
                }
            }

            const jaNoAcervo = dados.jaNoAcervo === '1';
            const badgeJa = document.getElementById('prev-google-ja-acervo-badge');
            const formImportar = document.getElementById('prev-google-form-importar');

            if (jaNoAcervo) {
                if (badgeJa) badgeJa.classList.remove('hidden');
                if (formImportar) formImportar.classList.add('hidden');
            } else {
                if (badgeJa) badgeJa.classList.add('hidden');
                if (formImportar) {
                    formImportar.classList.remove('hidden');
                    document.getElementById('prev-input-nome').value = dados.nome || '';
                    document.getElementById('prev-input-editora').value = dados.editora || '';
                    document.getElementById('prev-input-isbn').value = dados.isbn || '';
                    document.getElementById('prev-input-bibliografia').value = dados.sinopse || '';
                    document.getElementById('prev-input-imagem-capa').value = dados.capa || '';
                    document.getElementById('prev-input-preco').value = dados.rawPreco || '';

                    const autoresContainer = document.getElementById('prev-input-autores-container');
                    if (autoresContainer) {
                        autoresContainer.innerHTML = '';
                        autoresRaw.forEach(autor => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'autores[]';
                            input.value = autor;
                            autoresContainer.appendChild(input);
                        });
                    }
                }
            }

            const modal = document.getElementById('modal-previsualizar-livro-google');
            if (modal) modal.showModal();
        };
    </script>
</x-app-layout>