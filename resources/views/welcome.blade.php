<!DOCTYPE html>
<html lang="pt" data-theme="autumn">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Biblioteca') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-base-100 text-base-content flex flex-col justify-between">
        <header class="navbar bg-base-200 shadow-md px-4 lg:px-8 border-b border-base-300">
            <div class="navbar-start">
                <a href="{{ url('/') }}" class="btn btn-ghost normal-case text-2xl font-bold tracking-wide">
                    📚 {{ config('app.name', 'Biblioteca') }}
                </a>
            </div>

            <div class="navbar-end gap-3">
                @if (Route::has('login'))
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ url('/dashboard') }}" class="btn btn-ghost btn-sm">
                                Painel de Controlo
                            </a>
                        @endif
                        <a href="{{ route('requisicoes.index') }}" class="btn btn-ghost btn-sm">
                            Requisições
                        </a>
                        <a href="{{ route('carrinho.index') }}" class="btn btn-ghost btn-sm inline-flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span>{{ __('Carrinho') }}</span>
                            @if(auth()->user()->carrinhoItems()->sum('quantidade') > 0)
                                <span class="badge badge-primary badge-xs font-bold">{{ auth()->user()->carrinhoItems()->sum('quantidade') }}</span>
                            @endif
                        </a>
                        <a href="{{ route('profile.show') }}" class="btn btn-primary btn-sm">
                            O meu Perfil
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">
                            Entrar
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-primary btn-sm">
                                Registar
                            </a>
                        @endif
                    @endauth
                @endif
            </div>
        </header>

        <main class="container mx-auto px-4 py-8 flex flex-col gap-12">
            @if (session('status'))
                <div class="max-w-3xl mx-auto w-full alert alert-success text-white shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewbox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="max-w-3xl mx-auto w-full alert alert-error text-white shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewbox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <section class="max-w-3xl mx-auto w-full">
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold tracking-tight">Menus do Sistema</h2>
                    <p class="text-sm opacity-80 mt-1">Consulte os módulos estruturados da biblioteca</p>
                </div>

                <form method="GET" action="{{ url('/') }}" id="acordeao-menus-principais" class="join join-vertical w-full bg-base-200 border border-base-300 rounded-box shadow-md">
                    <details class="collapse collapse-arrow join-item border-b border-base-300">
                        <summary class="collapse-title text-lg font-bold flex items-center gap-2 cursor-pointer">
                            <span>📖</span> Livros (Ordenação e Preço)
                        </summary>
                        <div class="collapse-content flex flex-col gap-3 pt-2">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <div class="form-control">
                                    <label class="label cursor-pointer justify-start gap-3">
                                        <input type="radio" name="ordenar" value="livro_nome_asc" {{ in_array(request('ordenar', 'livro_nome_asc'), ['livro_nome_asc', 'nome_asc']) ? 'checked' : '' }} class="radio radio-primary radio-sm" />
                                        <span class="label-text text-sm">Nome do Livro (A a Z)</span>
                                    </label>
                                </div>
                                <div class="form-control">
                                    <label class="label cursor-pointer justify-start gap-3">
                                        <input type="radio" name="ordenar" value="livro_nome_desc" {{ in_array(request('ordenar'), ['livro_nome_desc', 'nome_desc']) ? 'checked' : '' }} class="radio radio-primary radio-sm" />
                                        <span class="label-text text-sm">Nome do Livro (Z a A)</span>
                                    </label>
                                </div>
                                <div class="form-control">
                                    <label class="label cursor-pointer justify-start gap-3">
                                        <input type="radio" name="ordenar" value="livro_isbn_asc" {{ in_array(request('ordenar'), ['livro_isbn_asc', 'isbn_asc']) ? 'checked' : '' }} class="radio radio-primary radio-sm" />
                                        <span class="label-text text-sm">ISBN (Crescente)</span>
                                    </label>
                                </div>
                                <div class="form-control">
                                    <label class="label cursor-pointer justify-start gap-3">
                                        <input type="radio" name="ordenar" value="livro_isbn_desc" {{ in_array(request('ordenar'), ['livro_isbn_desc', 'isbn_desc']) ? 'checked' : '' }} class="radio radio-primary radio-sm" />
                                        <span class="label-text text-sm">ISBN (Decrescente)</span>
                                    </label>
                                </div>
                            </div>

                            <div class="pt-2 border-t border-base-300">
                                <label class="label p-0 mb-1"><span class="label-text text-xs font-bold uppercase tracking-wider text-amber-950">Filtrar por Faixa de Preço (€)</span></label>
                                <div class="flex gap-2 items-center">
                                    <input type="number" step="0.01" name="preco_min" value="{{ request('preco_min') }}" placeholder="Preço Mínimo" class="input input-bordered input-sm w-1/2 bg-base-100 text-base-content text-xs" />
                                    <span class="text-xs font-bold opacity-50">-</span>
                                    <input type="number" step="0.01" name="preco_max" value="{{ request('preco_max') }}" placeholder="Preço Máximo" class="input input-bordered input-sm w-1/2 bg-base-100 text-base-content text-xs" />
                                </div>
                            </div>
                        </div>
                    </details>

                    <details class="collapse collapse-arrow join-item border-b border-base-300" {{ request('autores') || in_array(request('ordenar'), ['autor_nome_asc', 'autor_nome_desc']) ? 'open' : '' }}>
                        <summary class="collapse-title text-lg font-bold flex items-center gap-2 cursor-pointer">
                            <span>✍️</span> Autores (Ordenação e Seleção)
                        </summary>
                        <div class="collapse-content flex flex-col gap-3 pt-2">
                            <div class="flex gap-4 items-center">
                                <span class="text-xs font-bold uppercase tracking-wider text-amber-950">Ordenar Estante:</span>
                                <label class="label cursor-pointer gap-2 p-0">
                                    <input type="radio" name="ordenar" value="autor_nome_asc" {{ request('ordenar') === 'autor_nome_asc' ? 'checked' : '' }} class="radio radio-primary radio-xs" />
                                    <span class="label-text text-xs">Autor (A a Z)</span>
                                </label>
                                <label class="label cursor-pointer gap-2 p-0">
                                    <input type="radio" name="ordenar" value="autor_nome_desc" {{ request('ordenar') === 'autor_nome_desc' ? 'checked' : '' }} class="radio radio-primary radio-xs" />
                                    <span class="label-text text-xs">Autor (Z a A)</span>
                                </label>
                            </div>

                            <div>
                                <p class="text-xs opacity-75 mb-2">Selecione os autores que pretende ver na estante:</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-40 overflow-y-auto p-2 bg-base-100 rounded-lg">
                                    @foreach($autores as $autor)
                                        <label class="label cursor-pointer justify-start gap-3 p-1">
                                            <input type="checkbox" name="autores[]" value="{{ $autor->id }}" {{ in_array($autor->id, request('autores', [])) ? 'checked' : '' }} class="checkbox checkbox-primary checkbox-xs" />
                                            <span class="label-text text-xs">{{ $autor->nome }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </details>

                    <details class="collapse collapse-arrow join-item" {{ request('editoras') || in_array(request('ordenar'), ['editora_nome_asc', 'editora_nome_desc']) ? 'open' : '' }}>
                        <summary class="collapse-title text-lg font-bold flex items-center gap-2 cursor-pointer">
                            <span>🏢</span> Editoras (Ordenação e Seleção)
                        </summary>
                        <div class="collapse-content flex flex-col gap-3 pt-2">
                            <div class="flex gap-4 items-center">
                                <span class="text-xs font-bold uppercase tracking-wider text-amber-950">Ordenar Estante:</span>
                                <label class="label cursor-pointer gap-2 p-0">
                                    <input type="radio" name="ordenar" value="editora_nome_asc" {{ request('ordenar') === 'editora_nome_asc' ? 'checked' : '' }} class="radio radio-primary radio-xs" />
                                    <span class="label-text text-xs">Editora (A a Z)</span>
                                </label>
                                <label class="label cursor-pointer gap-2 p-0">
                                    <input type="radio" name="ordenar" value="editora_nome_desc" {{ request('ordenar') === 'editora_nome_desc' ? 'checked' : '' }} class="radio radio-primary radio-xs" />
                                    <span class="label-text text-xs">Editora (Z a A)</span>
                                </label>
                            </div>

                            <div>
                                <p class="text-xs opacity-75 mb-2">Selecione as editoras que pretende ver na estante:</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-40 overflow-y-auto p-2 bg-base-100 rounded-lg">
                                    @foreach($editoras as $editora)
                                        <label class="label cursor-pointer justify-start gap-3 p-1">
                                            <input type="checkbox" name="editoras[]" value="{{ $editora->id }}" {{ in_array($editora->id, request('editoras', [])) ? 'checked' : '' }} class="checkbox checkbox-primary checkbox-xs" />
                                            <span class="label-text text-xs">{{ $editora->nome }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </details>

                    <div class="p-4 bg-base-300 flex justify-end gap-3 rounded-b-box">
                        <a href="{{ url('/') }}" class="btn btn-sm btn-ghost">Repor Filtros e Ordenação</a>
                        <button type="submit" class="btn btn-sm btn-primary">Aplicar Filtros</button>
                    </div>
                </form>
            </section>

            <section class="max-w-2xl mx-auto w-full">
                <form method="GET" action="{{ url('/') }}" class="flex flex-col sm:flex-row gap-2 items-center">
                    @if(request('ordenar'))
                        <input type="hidden" name="ordenar" value="{{ request('ordenar') }}">
                    @endif
                    @if(request('preco_min'))
                        <input type="hidden" name="preco_min" value="{{ request('preco_min') }}">
                    @endif
                    @if(request('preco_max'))
                        <input type="hidden" name="preco_max" value="{{ request('preco_max') }}">
                    @endif
                    @foreach((array) request('autores', []) as $id)
                        <input type="hidden" name="autores[]" value="{{ $id }}">
                    @endforeach
                    @foreach((array) request('editoras', []) as $id)
                        <input type="hidden" name="editoras[]" value="{{ $id }}">
                    @endforeach

                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Pesquisar por título, autor, editora ou ISBN..." class="input input-bordered w-full bg-base-100 text-base-content text-sm shadow-md" />
                    <div class="flex gap-2 shrink-0 w-full sm:w-auto justify-end">
                        <button type="submit" class="btn btn-primary btn-sm">Pesquisar</button>
                        @if(request('search'))
                            <a href="{{ url('/') }}" class="btn btn-ghost btn-sm">Limpar</a>
                        @endif
                    </div>
                </form>
            </section>

            @php
                $servicoRecomendacao = app(App\Services\RecomendacaoLivroService::class);

                $estilosLombadas = [
                    'bg-gradient-to-r from-red-950 via-rose-950 to-red-950 border-amber-500/30 text-amber-100',
                    'bg-gradient-to-r from-blue-950 via-slate-900 to-indigo-950 border-amber-400/30 text-blue-100',
                    'bg-gradient-to-r from-emerald-950 via-teal-950 to-green-950 border-amber-400/30 text-emerald-100',
                    'bg-gradient-to-r from-amber-950 via-stone-900 to-yellow-950 border-amber-500/30 text-amber-100',
                    'bg-gradient-to-r from-purple-950 via-violet-950 to-neutral-900 border-amber-300/30 text-purple-100',
                    'bg-gradient-to-r from-slate-900 via-cyan-950 to-slate-900 border-cyan-400/30 text-slate-100',
                    'bg-gradient-to-r from-amber-900 via-orange-950 to-amber-950 border-amber-300/30 text-amber-100',
                    'bg-gradient-to-r from-neutral-950 via-stone-900 to-neutral-950 border-yellow-500/40 text-yellow-100',
                ];
            @endphp

            <section id="seccao-estante-livros" class="flex flex-col gap-6 max-w-5xl mx-auto w-full">
                <div class="text-center">
                    <h1 class="text-3xl font-extrabold tracking-tight lg:text-4xl">
                        Estante da Biblioteca
                    </h1>
                    <p class="text-base text-base-content opacity-80 mt-2">
                        Passe o rato pelas lombadas para as puxar da estante e clique num exemplar para abrir o livro.
                    </p>
                </div>

                <div id="armario-biblioteca" class="relative bg-amber-950/20 border-8 border-amber-950 rounded-2xl p-6 sm:p-8 lg:p-10 shadow-2xl flex flex-col gap-10">
                    @forelse($livrosPaginados->chunk(6) as $andar)
                        <div class="prateleira-nivel flex flex-col">
                            <div class="flex flex-row items-end justify-center gap-2 sm:gap-3 md:gap-3.5 min-h-[440px] px-2 sm:px-4">
                                @foreach($andar as $livro)
                                    @php
                                        $capaUrl = $livro->imagem_capa ? (Illuminate\Support\Str::startsWith($livro->imagem_capa, ['http://', 'https://']) ? $livro->imagem_capa : asset($livro->imagem_capa)) : '';
                                        $primeiroAutor = optional($livro->autores->first());
                                        $autorFotoUrl = $primeiroAutor->foto ? (Illuminate\Support\Str::startsWith($primeiroAutor->foto, ['http://', 'https://']) ? $primeiroAutor->foto : asset($primeiroAutor->foto)) : '';
                                        $editoraLogoUrl = optional($livro->editora)->logotipo ? (Illuminate\Support\Str::startsWith(optional($livro->editora)->logotipo, ['http://', 'https://']) ? optional($livro->editora)->logotipo : asset(optional($livro->editora)->logotipo)) : '';
                                        $historicoData = $livro->requisicoes->map(fn($r) => [
                                            'numero' => str_pad($r->numero_sequencial, 4, '0', STR_PAD_LEFT),
                                            'inicio' => $r->data_requisicao->format('d/m/Y'),
                                            'devolucao' => $r->data_devolucao ? $r->data_devolucao->format('d/m/Y') : null,
                                            'status' => $r->status,
                                        ])->values();

                                        $avaliacoesData = $livro->comentarios->where('status', 'ativo')->take(10)->map(fn($c) => [
                                            'nome' => optional($c->user)->name ?? 'Leitor',
                                            'estrelas' => (int) $c->classificacao,
                                            'comentario' => $c->comentario,
                                            'data' => $c->created_at->format('d/m/Y'),
                                        ])->values();

                                        $relacionadosData = $servicoRecomendacao->recomendar($livro, 3);
                                        $temAlertaAtivo = auth()->check() && $livro->alertas->where('user_id', auth()->id())->whereNull('notificado_at')->isNotEmpty();
                                    @endphp
                                    <div class="lombada-livro group relative h-[390px] sm:h-[420px] w-16 sm:w-18 md:w-20 rounded-r-sm rounded-l-xs shadow-xl hover:-translate-y-6 hover:scale-105 transition-all duration-300 cursor-pointer flex flex-col justify-between p-2 sm:p-2.5 border-l-4 border-r-2 border-t border-b overflow-hidden select-none {{ $estilosLombadas[$loop->index % count($estilosLombadas)] }}"
                                         data-titulo="{{ $livro->nome }}"
                                         data-autor="{{ $livro->autores->pluck('nome')->implode(', ') }}"
                                         data-editora="{{ $livro->editora->nome ?? '' }}"
                                         data-isbn="{{ $livro->isbn }}"
                                         data-preco="{{ is_numeric($livro->preco) ? number_format((float)$livro->preco, 2, ',', '.') . ' €' : $livro->preco }}"
                                         data-bibliografia="{{ $livro->bibliografia }}"
                                         data-capa="{{ $capaUrl }}"
                                         data-autor-foto="{{ $autorFotoUrl }}"
                                         data-editora-logotipo="{{ $editoraLogoUrl }}"
                                         data-disponivel="{{ $livro->isDisponivel() ? '1' : '0' }}"
                                         data-id="{{ $livro->id }}"
                                         data-historico="{{ json_encode($historicoData) }}"
                                         data-avaliacoes="{{ json_encode($avaliacoesData) }}"
                                         data-relacionados="{{ json_encode($relacionadosData) }}"
                                         data-tem-alerta="{{ $temAlertaAtivo ? '1' : '0' }}"
                                         onclick="abrirLivroPorElemento(this)">

                                        <div class="flex justify-center w-full mb-1">
                                            @if($livro->isDisponivel())
                                                <span class="badge badge-success text-white text-[8px] font-semibold px-1.5 py-0 h-4 min-h-0 border-0">
                                                    {{ __('Disponível') }}
                                                </span>
                                            @else
                                                <span class="badge badge-warning text-stone-900 text-[8px] font-semibold px-1.5 py-0 h-4 min-h-0 border-0">
                                                    {{ __('Requisitado') }}
                                                </span>
                                            @endif
                                        </div>
                                        
                                        <div class="lombada-autor w-full border-t border-b border-amber-400/30 py-1.5">
                                            <span class="block text-center font-sans font-semibold text-[9px] sm:text-[10px] leading-tight uppercase tracking-wider text-amber-200/90 break-normal">
                                                {{ $livro->autores->pluck('nome')->implode(', ') }}
                                            </span>
                                        </div>

                                        <div class="lombada-titulo grow flex items-center justify-center my-1 overflow-hidden w-full">
                                            @php
                                                $comprimento = mb_strlen($livro->nome);
                                                $tamanhoLetra = $comprimento <= 10 ? '20' : ($comprimento <= 14 ? '18' : ($comprimento <= 20 ? '15.5' : '14'));
                                            @endphp
                                            <svg class="w-full h-[300px] select-none pointer-events-none" viewBox="0 0 50 320">
                                                <text x="25" y="160" text-anchor="middle" dominant-baseline="middle" transform="rotate(90 25 160)" fill="#fff9e6" font-size="{{ $tamanhoLetra }}" font-family="Georgia, 'Times New Roman', ui-serif, serif" font-weight="800" letter-spacing="0.8">
                                                    {{ $livro->nome }}
                                                </text>
                                            </svg>
                                        </div>

                                        <div class="lombada-editora w-full border-t border-b border-amber-400/30 py-1.5">
                                            <span class="block text-center font-mono text-[9px] sm:text-[10px] leading-tight text-amber-300/80 break-normal">
                                                {{ $livro->editora->nome ?? '' }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="prateleira-tabua relative w-full mt-1">
                                <div class="h-6 bg-gradient-to-r from-amber-950 via-amber-800 to-amber-950 rounded-t-sm shadow-md border-t border-amber-600"></div>
                                <div class="h-4 bg-amber-950 rounded-b-sm shadow-2xl border-t border-black/40"></div>
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-info">
                            <span>Nenhum livro registado no armário da biblioteca.</span>
                        </div>
                    @endforelse
                </div>

                <div class="mt-8 flex justify-center">
                    {{ $livrosPaginados->links() }}
                </div>
            </section>
        </main>

        <dialog id="modal-detalhes-livro" class="modal">
            <div id="encadernacao-livro" class="modal-box max-w-5xl p-3 sm:p-5 bg-[#421522] border-4 border-[#2b0d16] shadow-2xl rounded-lg relative max-h-[92vh] overflow-y-auto">
                <form method="dialog">
                    <button class="absolute right-4 top-3 z-40 text-neutral-800 hover:text-black text-xl font-bold p-1 leading-none transition-colors">✕</button>
                </form>

                <div id="bloco-paginas-livro" class="relative rounded-sm bg-[#f4ebd0] border-l-8 border-r-8 border-[#d8caa6] shadow-2xl">
                    <div class="grid grid-cols-1 md:grid-cols-2 min-h-[500px]">
                        <div id="pagina-esquerda-livro" class="p-6 sm:p-8 flex flex-col items-center justify-between border-b md:border-b-0 md:border-r border-[#d4be94] shadow-[inset_-16px_0_24px_-8px_rgba(70,40,15,0.25)] overflow-y-auto max-h-[75vh] md:max-h-[82vh]">
                            <img id="modal-livro-capa" src="" alt="Capa" class="w-40 sm:w-48 h-56 sm:h-64 object-cover rounded shadow-lg border border-[#c5ad82] shrink-0">
                            <div class="w-full mt-4 flex flex-col gap-1.5 text-xs sm:text-sm text-neutral-900">
                                <p><span class="font-serif font-bold text-amber-950">Autor:</span> <span id="modal-livro-autor" class="font-serif"></span></p>
                                <p><span class="font-serif font-bold text-amber-950">Editora:</span> <span id="modal-livro-editora" class="font-serif"></span></p>
                                <p><span class="font-serif font-bold text-amber-950">ISBN:</span> <span id="modal-livro-isbn" class="font-mono"></span></p>
                                <div class="mt-2.5 flex items-center justify-between pt-2 border-t border-[#d8caa6]">
                                    <span class="font-serif font-bold text-amber-950">Preço:</span>
                                    <span id="modal-livro-preco" class="badge badge-primary font-bold"></span>
                                </div>
                                <div class="mt-2 flex items-center justify-between pt-2 border-t border-[#d8caa6]">
                                    <span class="font-serif font-bold text-amber-950">Estado:</span>
                                    <span id="modal-livro-disponibilidade" data-texto-disponivel="{{ __('Disponível para Requisição') }}" data-texto-indisponivel="{{ __('Indisponível para Requisição') }}" class="badge font-bold"></span>
                                </div>

                                <div id="modal-btn-requisitar-container" class="mt-4 pt-3 border-t border-[#d8caa6] w-full flex flex-col items-center gap-2">
                                    @auth
                                        <button type="button" id="modal-btn-requisitar" onclick="abrirModalConfirmacao()" data-texto-requisitar="{{ __('Requisitar Livro') }}" data-texto-indisponivel="{{ __('Indisponível para Requisição') }}" class="btn btn-primary w-full shadow-md">
                                            {{ __('Requisitar Livro') }}
                                        </button>

                                        <div id="container-alerta-disponibilidade" class="w-full hidden">
                                            <span id="badge-alerta-ativo" class="badge bg-sky-200 text-black border border-sky-400 font-black text-xs py-2.5 px-3 w-full justify-center shadow-sm hidden">
                                                🔔 {{ __('Notificação Ativa') }}
                                            </span>

                                            <form id="form-alerta-disponibilidade" method="POST" action="{{ route('alertas.store') }}" class="w-full hidden">
                                                @csrf
                                                <input type="hidden" name="livro_id" id="alerta-livro-id" value="">
                                                <button type="submit" class="btn btn-outline btn-warning btn-sm w-full font-bold shadow-sm text-xs">
                                                    🔔 {{ __('Notificar-me quando disponível') }}
                                                </button>
                                            </form>
                                        </div>

                                        <form id="form-adicionar-carrinho" method="POST" action="" class="w-full mt-1">
                                            @csrf
                                            <div class="join w-full shadow-sm">
                                                <input type="number" name="quantidade" id="input-carrinho-quantidade" value="1" min="1" class="input input-bordered input-sm join-item w-16 bg-[#fdfbf7] text-neutral-900 text-center font-bold text-xs" />
                                                <button type="submit" id="btn-adicionar-carrinho" class="btn btn-secondary btn-sm join-item grow font-bold text-xs shadow-md">
                                                    🛒 {{ __('Adicionar ao Carrinho') }}
                                                </button>
                                            </div>
                                        </form>
                                    @else
                                        <a href="{{ route('login') }}" class="btn btn-outline btn-primary w-full text-xs">
                                            {{ __('Iniciar Sessão para Requisitar') }}
                                        </a>
                                        <a href="{{ route('login') }}" class="btn btn-outline btn-secondary w-full text-xs mt-1">
                                            🛒 {{ __('Iniciar Sessão para Comprar') }}
                                        </a>
                                    @endauth
                                </div>
                            </div>
                        </div>

                        <div id="pagina-direita-livro" class="p-6 sm:p-8 flex flex-col justify-between shadow-[inset_16px_0_24px_-8px_rgba(70,40,15,0.25)] overflow-y-auto max-h-[75vh] md:max-h-[82vh]">
                            <div>
                                <h3 id="modal-livro-titulo" class="font-serif font-bold text-2xl sm:text-3xl text-amber-950 border-b-2 border-[#c5ad82] pb-2 leading-tight tracking-tight antialiased"></h3>
                                <h4 class="font-serif font-semibold text-xs uppercase tracking-widest text-amber-900 mt-4">Bibliografia & Sinopse</h4>
                                <p id="modal-livro-bibliografia" class="font-serif text-xs sm:text-sm text-neutral-800 leading-relaxed mt-2 text-justify overflow-y-auto max-h-36 sm:max-h-40 pr-2 antialiased"></p>

                                <div id="modal-livro-historico-bloco" class="mt-3 border-t border-[#c5ad82] pt-2">
                                    <details class="collapse collapse-arrow bg-amber-100/70 border border-[#c5ad82] rounded-md after:!top-1/2 after:!-translate-y-1/2">
                                        <summary class="collapse-title text-xs font-serif font-bold uppercase tracking-wider text-amber-950 py-2.5 min-h-0 cursor-pointer flex items-center gap-1 after:!top-[50%]">
                                            <span>📜</span> {{ __('Histórico de Requisições') }} (<span id="modal-livro-historico-total">0</span>)
                                        </summary>
                                        <div class="collapse-content px-2 py-1 max-h-32 overflow-y-auto">
                                            <div id="modal-livro-historico-vazio" class="text-xs text-neutral-600 italic py-1 text-center">
                                                {{ __('Sem histórico de requisições registadas.') }}
                                            </div>
                                            <div id="modal-livro-historico-lista" class="flex flex-col gap-1 hidden"></div>
                                        </div>
                                    </details>
                                </div>

                                <div id="modal-livro-avaliacoes-bloco" class="mt-2 border-t border-[#c5ad82] pt-2">
                                    <details class="collapse collapse-arrow bg-amber-100/70 border border-[#c5ad82] rounded-md after:!top-1/2 after:!-translate-y-1/2">
                                        <summary class="collapse-title text-xs font-serif font-bold uppercase tracking-wider text-amber-950 py-2.5 min-h-0 cursor-pointer flex items-center gap-1 after:!top-[50%]">
                                            <span>⭐</span> {{ __('Críticas dos Leitores') }} (<span id="modal-livro-avaliacoes-total">0</span>)
                                        </summary>
                                        <div class="collapse-content px-2 py-1 max-h-36 overflow-y-auto">
                                            <div id="modal-livro-avaliacoes-vazio" class="text-xs text-neutral-600 italic py-1 text-center">
                                                {{ __('Sem avaliações aprovadas até ao momento.') }}
                                            </div>
                                            <div id="modal-livro-avaliacoes-lista" class="flex flex-col gap-1.5 hidden"></div>
                                        </div>
                                    </details>
                                </div>

                                <div id="modal-livro-relacionados-bloco" data-texto-ver-obra="{{ __('ver obra') }}" class="mt-2 border-t border-[#c5ad82] pt-2">
                                    <details class="collapse collapse-arrow bg-amber-100/70 border border-[#c5ad82] rounded-md after:!top-1/2 after:!-translate-y-1/2">
                                        <summary class="collapse-title text-xs font-serif font-bold uppercase tracking-wider text-amber-950 py-2.5 min-h-0 cursor-pointer flex items-center gap-1 after:!top-[50%]">
                                            <span>📚</span> {{ __('Livros Relacionados') }} (<span id="modal-livro-relacionados-total">0</span>)
                                        </summary>
                                        <div class="collapse-content px-2 py-1 max-h-48 overflow-y-auto">
                                            <p class="text-[10px] text-neutral-600 italic mb-2 text-center">
                                                {{ __('Sugestões com base na temática e palavras-chave desta obra.') }}
                                            </p>
                                            <div id="modal-livro-relacionados-vazio" class="text-xs text-neutral-600 italic py-1 text-center">
                                                {{ __('Sem sugestões de livros relacionados.') }}
                                            </div>
                                            <div id="modal-livro-relacionados-lista" class="grid grid-cols-1 sm:grid-cols-3 gap-2 hidden"></div>
                                        </div>
                                    </details>
                                </div>
                            </div>
                            <div class="flex justify-between items-end mt-6 pt-4 border-t border-[#c5ad82]">
                                <div class="flex flex-col items-center gap-1.5">
                                    <span class="font-serif text-xs uppercase font-bold text-amber-900">Autor</span>
                                    <img id="modal-autor-foto" src="" alt="Autor" class="w-28 h-28 object-cover rounded-full shadow-lg border-2 border-[#c5ad82]">
                                </div>
                                <div class="flex flex-col items-center gap-1.5">
                                    <span class="font-serif text-xs uppercase font-bold text-amber-900">Editora</span>
                                    <div class="w-36 h-24 rounded-md bg-amber-100/90 border border-[#c5ad82] p-2 shadow-inner flex items-center justify-center">
                                        <img id="modal-editora-logotipo" src="" alt="Editora" class="w-full h-full object-contain drop-shadow-[0_1px_3px_rgba(0,0,0,0.75)]">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <form method="dialog" class="modal-backdrop">
                    <button>Fechar</button>
                </form>
            </dialog>

            @auth
                <dialog id="modal-confirmar-requisicao" class="modal">
                    <div class="modal-box max-w-lg bg-base-100 border border-base-300 shadow-2xl p-6">
                        <form method="dialog">
                            <button class="btn btn-sm btn-circle btn-ghost absolute right-3 top-3">✕</button>
                        </form>

                        <h3 class="font-bold text-lg text-base-content border-b border-base-200 pb-2">
                            {{ __('Confirmar Requisição de Livro') }}
                        </h3>

                        <form id="form-confirmar-requisicao" method="POST" action="{{ route('requisicoes.store') }}" class="mt-4 flex flex-col gap-4">
                            @csrf
                            <input type="hidden" name="livro_id" id="input-requisicao-livro-id" value="">

                            <div class="flex items-center gap-4 bg-base-200 p-3 rounded-lg border border-base-300">
                                <img id="confirmar-livro-capa" src="" alt="Capa" class="w-16 h-20 object-cover rounded shadow border border-base-300">
                                <div class="flex flex-col">
                                    <span class="text-xs font-semibold text-base-content/60">{{ __('Obra Selecionada') }}</span>
                                    <span id="confirmar-livro-titulo" class="font-bold text-sm text-base-content"></span>
                                </div>
                            </div>

                            <div class="flex flex-col gap-1 border-t border-base-200 pt-3">
                                <span class="text-xs font-bold uppercase tracking-wider text-base-content/70">{{ __('Dados do Requisitante') }}</span>
                                <div class="flex items-center gap-3 bg-base-200/50 p-2.5 rounded-lg border border-base-300">
                                    <img id="confirmar-cidadao-foto" src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}" class="w-12 h-12 rounded-full object-cover border border-primary">
                                    <div class="flex flex-col">
                                        <span id="confirmar-cidadao-nome" class="text-sm font-bold text-base-content">{{ auth()->user()->name }}</span>
                                        <span class="text-xs text-base-content/70">{{ auth()->user()->email }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col gap-1 border-t border-base-200 pt-3">
                                <span class="text-xs font-bold uppercase tracking-wider text-base-content/70">{{ __('Período da Requisição') }}</span>
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <div class="bg-base-200/50 p-2.5 rounded-lg border border-base-300 flex flex-col gap-1">
                                        <span class="text-base-content/70">{{ __('Data de Início') }}:</span>
                                        <span id="confirmar-data-inicio" class="badge badge-outline text-xs font-mono font-bold">{{ now()->format('d/m/Y') }}</span>
                                    </div>
                                    <div class="bg-base-200/50 p-2.5 rounded-lg border border-base-300 flex flex-col gap-1">
                                        <span class="text-base-content/70">{{ __('Data de Devolução Prevista (+5 dias)') }}:</span>
                                        <span id="confirmar-data-devolucao" class="badge badge-outline text-xs font-mono font-bold text-primary">{{ now()->addDays(5)->format('d/m/Y') }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-action border-t border-base-200 pt-3 mt-2 flex justify-end gap-2">
                                <button type="button" onclick="document.getElementById('modal-confirmar-requisicao').close()" class="btn btn-ghost btn-sm">
                                    {{ __('Cancelar') }}
                                </button>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    {{ __('Confirmar Requisição') }}
                                </button>
                            </div>
                        </form>
                    </div>
                    <form method="dialog" class="modal-backdrop">
                        <button>{{ __('Fechar') }}</button>
                    </form>
                </dialog>
            @endauth

            <footer class="footer footer-center p-6 bg-base-200 text-base-content border-t border-base-300 mt-12">
                <div>
                    <p class="text-sm font-medium">Projeto Biblioteca © 2026 - Inovcorp</p>
                </div>
            </footer>

            <script>
                function abrirLivroPorElemento(elemento) {
                    preencherModalComDados(elemento.dataset);
                }

                function preencherModalComDados(dados) {
                    document.getElementById('modal-livro-titulo').textContent = dados.titulo || '';
                    document.getElementById('modal-livro-autor').textContent = dados.autor || '';
                    document.getElementById('modal-livro-editora').textContent = dados.editora || '';
                    document.getElementById('modal-livro-isbn').textContent = dados.isbn || '';
                    document.getElementById('modal-livro-preco').textContent = dados.preco || '';
                    document.getElementById('modal-livro-bibliografia').textContent = dados.bibliografia || '';
                    document.getElementById('modal-livro-capa').src = dados.capa || '';

                    const badgeDisponivel = document.getElementById('modal-livro-disponibilidade');
                    if (dados.disponivel === '1') {
                        badgeDisponivel.textContent = badgeDisponivel.dataset.textoDisponivel;
                        badgeDisponivel.classList.remove('badge-warning', 'text-stone-900');
                        badgeDisponivel.classList.add('badge-success', 'text-white');
                    } else {
                        badgeDisponivel.textContent = badgeDisponivel.dataset.textoIndisponivel;
                        badgeDisponivel.classList.remove('badge-success', 'text-white');
                        badgeDisponivel.classList.add('badge-warning', 'text-stone-900');
                    }

                    const imgAutor = document.getElementById('modal-autor-foto');
                    if (dados.autorFoto) {
                        imgAutor.src = dados.autorFoto;
                        imgAutor.classList.remove('hidden');
                    } else {
                        imgAutor.src = '';
                        imgAutor.classList.add('hidden');
                    }

                    const imgEditora = document.getElementById('modal-editora-logotipo');
                    if (dados.editoraLogotipo) {
                        imgEditora.src = dados.editoraLogotipo;
                        imgEditora.classList.remove('hidden');
                    } else {
                        imgEditora.src = '';
                        imgEditora.classList.add('hidden');
                    }

                    const btnReq = document.getElementById('modal-btn-requisitar');
                    if (btnReq) {
                        if (dados.disponivel === '1') {
                            btnReq.disabled = false;
                            btnReq.className = 'btn btn-primary w-full shadow-md';
                            btnReq.textContent = btnReq.dataset.textoRequisitar;
                        } else {
                            btnReq.disabled = true;
                            btnReq.className = 'btn bg-amber-900/20 text-amber-950 border border-amber-950/40 cursor-not-allowed font-bold w-full';
                            btnReq.textContent = btnReq.dataset.textoIndisponivel;
                        }
                    }

                    const inputLivroId = document.getElementById('input-requisicao-livro-id');
                    if (inputLivroId) {
                        inputLivroId.value = dados.id;
                    }

                    const formCarrinho = document.getElementById('form-adicionar-carrinho');
                    if (formCarrinho) {
                        formCarrinho.action = '/carrinho/' + dados.id;
                    }

                    const inputQtd = document.getElementById('input-carrinho-quantidade');
                    if (inputQtd) {
                        inputQtd.value = 1;
                    }

                    const confirmarTitulo = document.getElementById('confirmar-livro-titulo');
                    if (confirmarTitulo) {
                        confirmarTitulo.textContent = dados.titulo || '';
                    }

                    const confirmarCapa = document.getElementById('confirmar-livro-capa');
                    if (confirmarCapa) {
                        confirmarCapa.src = dados.capa || '';
                    }

                    const containerAlerta = document.getElementById('container-alerta-disponibilidade');
                    const badgeAlerta = document.getElementById('badge-alerta-ativo');
                    const formAlerta = document.getElementById('form-alerta-disponibilidade');
                    const inputAlertaLivroId = document.getElementById('alerta-livro-id');

                    if (containerAlerta) {
                        if (dados.disponivel === '1') {
                            containerAlerta.classList.add('hidden');
                        } else {
                            containerAlerta.classList.remove('hidden');
                            if (inputAlertaLivroId) inputAlertaLivroId.value = dados.id;

                            if (dados.temAlerta === '1') {
                                if (badgeAlerta) badgeAlerta.classList.remove('hidden');
                                if (formAlerta) formAlerta.classList.add('hidden');
                            } else {
                                if (badgeAlerta) badgeAlerta.classList.add('hidden');
                                if (formAlerta) formAlerta.classList.remove('hidden');
                            }
                        }
                    }

                    const historico = Array.isArray(dados.historico) ? dados.historico : JSON.parse(dados.historico || '[]');
                    const totalSpan = document.getElementById('modal-livro-historico-total');
                    const listaContainer = document.getElementById('modal-livro-historico-lista');
                    const vazioMsg = document.getElementById('modal-livro-historico-vazio');

                    if (totalSpan) {
                        totalSpan.textContent = historico.length;
                    }

                    if (listaContainer && vazioMsg) {
                        listaContainer.innerHTML = '';
                        if (historico.length === 0) {
                            vazioMsg.classList.remove('hidden');
                            listaContainer.classList.add('hidden');
                        } else {
                            vazioMsg.classList.add('hidden');
                            listaContainer.classList.remove('hidden');
                            historico.forEach(function(item) {
                                const row = document.createElement('div');
                                row.className = 'flex items-center justify-between text-xs p-1 bg-base-100/90 rounded border border-[#d8caa6]';

                                const spanNum = document.createElement('span');
                                spanNum.className = 'font-mono font-bold text-amber-950';
                                spanNum.textContent = '#' + item.numero;

                                const spanData = document.createElement('span');
                                spanData.className = 'text-[11px] font-mono text-neutral-700';
                                spanData.textContent = item.devolucao ? item.inicio + ' → ' + item.devolucao : item.inicio;

                                const spanBadge = document.createElement('span');
                                let badgeClass = 'badge-warning text-stone-900';
                                let badgeText = 'Ativa';
                                if (item.status === 'entregue') {
                                    badgeClass = 'badge-success text-white';
                                    badgeText = 'Entregue';
                                } else if (item.status === 'cancelada') {
                                    badgeClass = 'badge-ghost';
                                    badgeText = 'Cancelada';
                                }
                                spanBadge.className = 'badge ' + badgeClass + ' badge-xs font-bold';
                                spanBadge.textContent = badgeText;

                                row.appendChild(spanNum);
                                row.appendChild(spanData);
                                row.appendChild(spanBadge);
                                listaContainer.appendChild(row);
                            });
                        }
                    }

                    const avaliacoes = Array.isArray(dados.avaliacoes) ? dados.avaliacoes : JSON.parse(dados.avaliacoes || '[]');
                    const totalAvaliacoesSpan = document.getElementById('modal-livro-avaliacoes-total');
                    const listaAvaliacoes = document.getElementById('modal-livro-avaliacoes-lista');
                    const vazioAvaliacoesMsg = document.getElementById('modal-livro-avaliacoes-vazio');

                    if (totalAvaliacoesSpan) {
                        totalAvaliacoesSpan.textContent = avaliacoes.length;
                    }

                    if (listaAvaliacoes && vazioAvaliacoesMsg) {
                        listaAvaliacoes.innerHTML = '';
                        if (avaliacoes.length === 0) {
                            vazioAvaliacoesMsg.classList.remove('hidden');
                            listaAvaliacoes.classList.add('hidden');
                        } else {
                            vazioAvaliacoesMsg.classList.add('hidden');
                            listaAvaliacoes.classList.remove('hidden');
                            avaliacoes.forEach(function(item) {
                                const card = document.createElement('div');
                                card.className = 'flex flex-col gap-0.5 p-2 bg-base-100/90 rounded border border-[#d8caa6] text-xs';

                                const topRow = document.createElement('div');
                                topRow.className = 'flex items-center justify-between';

                                const nomeEl = document.createElement('span');
                                nomeEl.className = 'font-bold text-base-content';
                                nomeEl.textContent = item.nome;

                                const estrelasEl = document.createElement('span');
                                estrelasEl.className = 'text-amber-600 text-xs';
                                estrelasEl.textContent = '⭐'.repeat(item.estrelas || 5);

                                topRow.appendChild(nomeEl);
                                topRow.appendChild(estrelasEl);

                                const comentarioEl = document.createElement('p');
                                comentarioEl.className = 'text-neutral-700 italic text-[11px] leading-relaxed text-justify';
                                comentarioEl.textContent = '"' + item.comentario + '"';

                                const dataEl = document.createElement('span');
                                dataEl.className = 'text-[9px] text-neutral-500 font-mono text-right';
                                dataEl.textContent = item.data;

                                card.appendChild(topRow);
                                card.appendChild(comentarioEl);
                                card.appendChild(dataEl);

                                listaAvaliacoes.appendChild(card);
                            });
                        }
                    }

                    const relacionados = Array.isArray(dados.relacionados) ? dados.relacionados : JSON.parse(dados.relacionados || '[]');
                    const totalRelacionadosSpan = document.getElementById('modal-livro-relacionados-total');
                    const listaRelacionados = document.getElementById('modal-livro-relacionados-lista');
                    const vazioRelacionadosMsg = document.getElementById('modal-livro-relacionados-vazio');

                    if (totalRelacionadosSpan) {
                        totalRelacionadosSpan.textContent = relacionados.length;
                    }

                    if (listaRelacionados && vazioRelacionadosMsg) {
                        listaRelacionados.innerHTML = '';
                        if (relacionados.length === 0) {
                            vazioRelacionadosMsg.classList.remove('hidden');
                            listaRelacionados.classList.add('hidden');
                        } else {
                            vazioRelacionadosMsg.classList.add('hidden');
                            listaRelacionados.classList.remove('hidden');
                            relacionados.forEach(function(item) {
                                const card = document.createElement('div');
                                card.className = 'flex flex-col justify-between p-2 bg-base-100/90 rounded border border-[#d8caa6] shadow-sm hover:border-primary transition-all text-xs';

                                const topDiv = document.createElement('div');
                                topDiv.className = 'flex items-center gap-2';

                                if (item.capa) {
                                    const img = document.createElement('img');
                                    img.src = item.capa;
                                    img.alt = item.nome;
                                    img.className = 'w-8 h-11 object-cover rounded shadow border border-[#d8caa6] shrink-0';
                                    topDiv.appendChild(img);
                                } else {
                                    const icon = document.createElement('div');
                                    icon.className = 'w-8 h-11 bg-base-300 rounded flex items-center justify-center text-xs shrink-0';
                                    icon.textContent = '📖';
                                    topDiv.appendChild(icon);
                                }

                                const infoDiv = document.createElement('div');
                                infoDiv.className = 'flex flex-col grow overflow-hidden';

                                const tituloEl = document.createElement('span');
                                tituloEl.className = 'font-bold text-[11px] text-base-content truncate leading-tight';
                                tituloEl.textContent = item.nome;

                                const autorEl = document.createElement('span');
                                autorEl.className = 'text-[9px] text-neutral-600 truncate';
                                autorEl.textContent = item.autor;

                                infoDiv.appendChild(tituloEl);
                                infoDiv.appendChild(autorEl);
                                topDiv.appendChild(infoDiv);
                                card.appendChild(topDiv);

                                const blocoRelacionados = document.getElementById('modal-livro-relacionados-bloco');
                                const textoVerObra = (blocoRelacionados && blocoRelacionados.dataset.textoVerObra) ? blocoRelacionados.dataset.textoVerObra : 'Ver Obra';

                                const btn = document.createElement('button');
                                btn.type = 'button';
                                btn.className = 'btn btn-outline btn-primary btn-xs mt-2 w-full text-[10px] font-bold';
                                btn.textContent = '👁️ ' + textoVerObra;
                                btn.onclick = function() {
                                    window.abrirLivroRelacionado(item.id);
                                };

                                card.appendChild(btn);
                                listaRelacionados.appendChild(card);
                            });
                        }
                    }

                    document.getElementById('modal-detalhes-livro').showModal();
                }

                window.abrirLivroRelacionado = function(livroId) {
                    const lombada = document.querySelector('.lombada-livro[data-id="' + livroId + '"]');
                    if (lombada) {
                        abrirLivroPorElemento(lombada);
                    } else {
                        fetch('/livros/' + livroId + '/detalhes')
                            .then(function(res) {
                                if (!res.ok) {
                                    throw new Error('Falha ao obter dados da obra');
                                }
                                return res.json();
                            })
                            .then(function(dados) {
                                preencherModalComDados(dados);
                            })
                            .catch(function(err) {
                                console.error('Erro ao carregar livro relacionado:', err);
                            });
                    }
                };

                function abrirModalConfirmacao() {
                    const modalConfirmacao = document.getElementById('modal-confirmar-requisicao');
                    if (modalConfirmacao) {
                        modalConfirmacao.showModal();
                    }
                }
            </script>
        </body>
    </html>