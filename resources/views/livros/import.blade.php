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
                        <input type="text" id="input-query-google" name="query" value="{{ $query ?? '' }}" required placeholder="{{ __('Pesquise por título, autor, editora ou ISBN...') }}" class="input input-bordered input-sm join-item grow bg-base-100 text-base-content" />
                        <button type="submit" class="btn btn-primary btn-sm join-item">
                            🔍 {{ __('Pesquisar na Google') }}
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

                                <div class="flex items-center justify-between mt-4 pt-3 border-t border-base-200">
                                    @if($item['ja_no_acervo'])
                                        <span class="badge badge-success text-white font-bold gap-1 text-xs">
                                            ✓ {{ __('Já no Acervo') }}
                                        </span>
                                        <button type="button" disabled class="btn btn-disabled btn-sm text-xs">
                                            {{ __('Importado') }}
                                        </button>
                                    @else
                                        <span class="badge badge-outline badge-sm text-[11px] font-semibold text-base-content/70">
                                            {{ __('Novo exemplar') }}
                                        </span>
                                        <form method="POST" action="{{ route('livros.importar.store') }}">
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
                                                📥 {{ __('Importar para o Acervo') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-info shadow-lg">
                        <span>ℹ️ {{ __('Nenhum livro encontrado na Google Books para a pesquisa indicada. Tente pesquisar por outro termo, autor ou ISBN.') }}</span>
                    </div>
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
</x-app-layout>