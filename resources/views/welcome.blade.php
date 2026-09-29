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
                        <a href="{{ url('/dashboard') }}" class="btn btn-primary btn-sm">
                            Dashboard
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
            <section class="max-w-3xl mx-auto w-full">
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold tracking-tight">Menus do Sistema</h2>
                    <p class="text-sm opacity-80 mt-1">Consulte os módulos estruturados da biblioteca</p>
                </div>

                <form method="GET" action="{{ url('/') }}" id="acordeao-menus-principais" class="join join-vertical w-full bg-base-200 border border-base-300 rounded-box shadow-md">
                    <details class="collapse collapse-arrow join-item border-b border-base-300" {{ !request('ordenar') && !request('excluir_autores') && !request('excluir_editoras') ? 'open' : '' }}>
                        <summary class="collapse-title text-lg font-bold flex items-center gap-2 cursor-pointer">
                            <span>📖</span> Livros (Ordenação)
                        </summary>
                        <div class="collapse-content flex flex-col gap-3 pt-2">
                            <div class="form-control">
                                <label class="label cursor-pointer justify-start gap-3">
                                    <input type="radio" name="ordenar" value="nome_asc" {{ request('ordenar', 'nome_asc') === 'nome_asc' ? 'checked' : '' }} class="radio radio-primary radio-sm" />
                                    <span class="label-text text-sm">Ordenar por Nome (A a Z)</span>
                                </label>
                            </div>
                            <div class="form-control">
                                <label class="label cursor-pointer justify-start gap-3">
                                    <input type="radio" name="ordenar" value="nome_desc" {{ request('ordenar') === 'nome_desc' ? 'checked' : '' }} class="radio radio-primary radio-sm" />
                                    <span class="label-text text-sm">Ordenar por Nome (Z a A)</span>
                                </label>
                            </div>
                            <div class="form-control">
                                <label class="label cursor-pointer justify-start gap-3">
                                    <input type="radio" name="ordenar" value="isbn_asc" {{ request('ordenar') === 'isbn_asc' ? 'checked' : '' }} class="radio radio-primary radio-sm" />
                                    <span class="label-text text-sm">Ordenar por ISBN (Crescente)</span>
                                </label>
                            </div>
                            <div class="form-control">
                                <label class="label cursor-pointer justify-start gap-3">
                                    <input type="radio" name="ordenar" value="isbn_desc" {{ request('ordenar') === 'isbn_desc' ? 'checked' : '' }} class="radio radio-primary radio-sm" />
                                    <span class="label-text text-sm">Ordenar por ISBN (Decrescente)</span>
                                </label>
                            </div>
                        </div>
                    </details>

                    <details class="collapse collapse-arrow join-item border-b border-base-300" {{ request('excluir_autores') ? 'open' : '' }}>
                        <summary class="collapse-title text-lg font-bold flex items-center gap-2 cursor-pointer">
                            <span>✍️</span> Autores (Filtrar / Excluir)
                        </summary>
                        <div class="collapse-content flex flex-col gap-2 pt-2">
                            <p class="text-xs opacity-75 mb-2">Selecione os autores que pretende excluir da estante:</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-40 overflow-y-auto p-2 bg-base-100 rounded-lg">
                                @foreach($autores as $autor)
                                    <label class="label cursor-pointer justify-start gap-3 p-1">
                                        <input type="checkbox" name="excluir_autores[]" value="{{ $autor->id }}" {{ in_array($autor->id, request('excluir_autores', [])) ? 'checked' : '' }} class="checkbox checkbox-primary checkbox-xs" />
                                        <span class="label-text text-xs">{{ $autor->nome }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </details>

                    <details class="collapse collapse-arrow join-item" {{ request('excluir_editoras') ? 'open' : '' }}>
                        <summary class="collapse-title text-lg font-bold flex items-center gap-2 cursor-pointer">
                            <span>🏢</span> Editoras (Filtrar / Excluir)
                        </summary>
                        <div class="collapse-content flex flex-col gap-2 pt-2">
                            <p class="text-xs opacity-75 mb-2">Selecione as editoras que pretende excluir da estante:</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-40 overflow-y-auto p-2 bg-base-100 rounded-lg">
                                @foreach($editoras as $editora)
                                    <label class="label cursor-pointer justify-start gap-3 p-1">
                                        <input type="checkbox" name="excluir_editoras[]" value="{{ $editora->id }}" {{ in_array($editora->id, request('excluir_editoras', [])) ? 'checked' : '' }} class="checkbox checkbox-primary checkbox-xs" />
                                        <span class="label-text text-xs">{{ $editora->nome }}</span>
                                    </label>
                                @endforeach
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
                    @foreach((array) request('excluir_autores', []) as $id)
                        <input type="hidden" name="excluir_autores[]" value="{{ $id }}">
                    @endforeach
                    @foreach((array) request('excluir_editoras', []) as $id)
                        <input type="hidden" name="excluir_editoras[]" value="{{ $id }}">
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
                    @forelse($livros->chunk(6) as $andar)
                        <div class="prateleira-nivel flex flex-col">
                            <div class="flex flex-row items-end justify-center gap-2 sm:gap-3 md:gap-3.5 min-h-[440px] px-2 sm:px-4">
                                @foreach($andar as $livro)
                                    @php
                                        $capaUrl = $livro->imagem_capa ? (Illuminate\Support\Str::startsWith($livro->imagem_capa, ['http://', 'https://']) ? $livro->imagem_capa : asset($livro->imagem_capa)) : '';
                                        $primeiroAutor = optional($livro->autores->first());
                                        $autorFotoUrl = $primeiroAutor->foto ? (Illuminate\Support\Str::startsWith($primeiroAutor->foto, ['http://', 'https://']) ? $primeiroAutor->foto : asset($primeiroAutor->foto)) : '';
                                        $editoraLogoUrl = optional($livro->editora)->logotipo ? (Illuminate\Support\Str::startsWith(optional($livro->editora)->logotipo, ['http://', 'https://']) ? optional($livro->editora)->logotipo : asset(optional($livro->editora)->logotipo)) : '';
                                    @endphp
                                    <div class="lombada-livro group relative h-[390px] sm:h-[420px] w-16 sm:w-18 md:w-20 rounded-r-sm rounded-l-xs shadow-xl hover:-translate-y-6 hover:scale-105 transition-all duration-300 cursor-pointer flex flex-col justify-between p-2 sm:p-2.5 border-l-4 border-r-2 border-t border-b overflow-hidden select-none {{ $estilosLombadas[$loop->index % count($estilosLombadas)] }}"
                                         data-titulo="{{ $livro->nome }}"
                                         data-autor="{{ $livro->autores->pluck('nome')->implode(', ') }}"
                                         data-editora="{{ $livro->editora->nome ?? '' }}"
                                         data-isbn="{{ $livro->isbn }}"
                                         data-preco="{{ number_format($livro->preco, 2, ',', '.') }} €"
                                         data-bibliografia="{{ $livro->bibliografia }}"
                                         data-capa="{{ $capaUrl }}"
                                         data-autor-foto="{{ $autorFotoUrl }}"
                                         data-editora-logotipo="{{ $editoraLogoUrl }}"
                                         onclick="abrirLivroPorElemento(this)">
                                        
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
            </section>
        </main>

        <dialog id="modal-detalhes-livro" class="modal">
            <div id="encadernacao-livro" class="modal-box max-w-5xl p-3 sm:p-5 bg-[#421522] border-4 border-[#2b0d16] shadow-2xl rounded-lg relative overflow-hidden">
                <form method="dialog">
                    <button class="absolute right-4 top-3 z-40 text-neutral-800 hover:text-black text-xl font-bold p-1 leading-none transition-colors">✕</button>
                </form>

                <div id="bloco-paginas-livro" class="relative rounded-sm bg-[#f4ebd0] border-l-8 border-r-8 border-[#d8caa6] shadow-2xl overflow-hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2 min-h-[500px]">
                        <div id="pagina-esquerda-livro" class="p-6 sm:p-8 flex flex-col items-center justify-between border-b md:border-b-0 md:border-r border-[#d4be94] shadow-[inset_-16px_0_24px_-8px_rgba(70,40,15,0.25)]">
                            <img id="modal-livro-capa" src="" alt="Capa" class="w-40 sm:w-48 h-56 sm:h-64 object-cover rounded shadow-lg border border-[#c5ad82] shrink-0">
                            <div class="w-full mt-4 flex flex-col gap-1.5 text-xs sm:text-sm text-neutral-900">
                                <p><span class="font-serif font-bold text-amber-950">Autor:</span> <span id="modal-livro-autor" class="font-serif"></span></p>
                                <p><span class="font-serif font-bold text-amber-950">Editora:</span> <span id="modal-livro-editora" class="font-serif"></span></p>
                                <p><span class="font-serif font-bold text-amber-950">ISBN:</span> <span id="modal-livro-isbn" class="font-mono"></span></p>
                                <div class="mt-2.5 flex items-center justify-between pt-2 border-t border-[#d8caa6]">
                                    <span class="font-serif font-bold text-amber-950">Preço:</span>
                                    <span id="modal-livro-preco" class="badge badge-primary font-bold"></span>
                                </div>
                            </div>
                        </div>

                        <div id="pagina-direita-livro" class="p-6 sm:p-8 flex flex-col justify-between shadow-[inset_16px_0_24px_-8px_rgba(70,40,15,0.25)]">
                            <div>
                                <h3 id="modal-livro-titulo" class="font-serif font-bold text-2xl sm:text-3xl text-amber-950 border-b-2 border-[#c5ad82] pb-2 leading-tight tracking-tight antialiased"></h3>
                                <h4 class="font-serif font-semibold text-xs uppercase tracking-widest text-amber-900 mt-4">Bibliografia & Sinopse</h4>
                                <p id="modal-livro-bibliografia" class="font-serif text-xs sm:text-sm text-neutral-800 leading-relaxed mt-2 text-justify overflow-y-auto max-h-56 sm:max-h-60 pr-2 antialiased"></p>
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
            </div>
            <form method="dialog" class="modal-backdrop">
                <button>Fechar</button>
            </form>
        </dialog>

        <footer class="footer footer-center p-6 bg-base-200 text-base-content border-t border-base-300 mt-12">
            <div>
                <p class="text-sm font-medium">Projeto Biblioteca © 2026 - Inovcorp</p>
            </div>
        </footer>

        <script>
            function abrirLivroPorElemento(elemento) {
                const dados = elemento.dataset;
                document.getElementById('modal-livro-titulo').textContent = dados.titulo;
                document.getElementById('modal-livro-autor').textContent = dados.autor;
                document.getElementById('modal-livro-editora').textContent = dados.editora;
                document.getElementById('modal-livro-isbn').textContent = dados.isbn;
                document.getElementById('modal-livro-preco').textContent = dados.preco;
                document.getElementById('modal-livro-bibliografia').textContent = dados.bibliografia;
                document.getElementById('modal-livro-capa').src = dados.capa;

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

                document.getElementById('modal-detalhes-livro').showModal();
            }
        </script>
    </body>
</html>