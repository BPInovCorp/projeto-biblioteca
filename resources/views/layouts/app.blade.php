<!DOCTYPE html>
<html lang="pt" data-theme="autumn">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Biblioteca') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased min-h-screen bg-base-200 text-base-content flex flex-col justify-between">
        <x-banner />

        <header class="navbar bg-base-100 shadow-md px-4 sm:px-8 border-b border-base-300">
            <div class="navbar-start">
                <a href="{{ url('/') }}" class="btn btn-ghost normal-case text-xl sm:text-2xl font-bold tracking-wide shrink-0">
                    📚 {{ config('app.name', 'Biblioteca') }}
                </a>
            </div>

            <div class="navbar-end gap-2">
                <a href="{{ route('carrinho.index') }}" class="btn btn-ghost btn-sm inline-flex items-center gap-1.5 px-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>Carrinho</span>
                    @if(auth()->user()?->carrinhoItems()->sum('quantidade') > 0)
                        <span class="badge badge-primary badge-xs font-bold">{{ auth()->user()->carrinhoItems()->sum('quantidade') }}</span>
                    @endif
                </a>

                <div class="relative" x-data="{ aberto: false }">
                    <button type="button" @click="aberto = !aberto" class="btn btn-ghost btn-square btn-sm" aria-label="Menu de Navegação">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div x-show="aberto" x-cloak @click.outside="aberto = false" class="absolute right-0 top-full mt-2 z-50 bg-base-100 rounded-2xl border border-base-300 shadow-2xl w-60 p-3">
                        <ul class="menu menu-sm gap-1 font-semibold text-xs">
                            <li><a href="{{ url('/') }}" @click="aberto = false">📖 Estante</a></li>
                            <li><a href="{{ route('requisicoes.index') }}" @click="aberto = false">📜 Requisições</a></li>
                            @if(request()->user()?->isAdmin())
                                <li class="menu-title text-[10px] uppercase font-bold text-base-content/60 mt-1">Administração</li>
                                <li><a href="{{ route('dashboard') }}" @click="aberto = false">⚙️ Painel de Controlo</a></li>
                                <li><a href="{{ route('admin.logs.index') }}" @click="aberto = false">📋 Logs de Auditoria</a></li>
                            @endif
                            <div class="divider my-1"></div>
                            <li><a href="{{ route('profile.show') }}" @click="aberto = false">👤 O meu Perfil</a></li>
                            <li><button type="button" onclick="document.getElementById('modal-confirmar-logout').showModal()" class="text-error font-bold">🚪 Terminar Sessão</button></li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        @if (session('success'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
                <div class="alert alert-success text-white shadow-md flex items-center gap-2 text-xs sm:text-sm font-bold">
                    <span>✅</span> {{ session('success') }}
                </div>
            </div>
        @endif

        @if (session('status'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
                <div class="alert alert-success text-white shadow-md flex items-center gap-2 text-xs sm:text-sm font-bold">
                    <span>✅</span> {{ session('status') }}
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
                <div class="alert alert-error text-white shadow-md flex items-center gap-2 text-xs sm:text-sm font-bold">
                    <span>⚠️</span> {{ session('error') }}
                </div>
            </div>
        @endif

        <main class="grow">
            {{ $slot }}
        </main>

        <footer class="footer footer-center p-6 bg-base-100 text-base-content border-t border-base-300 mt-12">
            <div>
                <p class="text-sm font-medium">Projeto Biblioteca 2026 - Inovcorp - Desenvolvido por Bruno Pinto</p>
            </div>
        </footer>

        <dialog id="modal-confirmar-logout" class="modal">
            <div class="modal-box bg-base-100 border border-base-300 shadow-2xl max-w-md text-center p-6 flex flex-col items-center gap-3">
                <div class="text-4xl">🚪</div>
                <h3 class="font-bold text-lg text-amber-950">Terminar sessão</h3>
                <p class="text-xs opacity-80 leading-relaxed">
                    Tem a certeza de que pretende encerrar a sua sessão na biblioteca?
                </p>
                <div class="modal-action flex justify-center gap-3 mt-4 w-full">
                    <form method="dialog">
                        <button class="btn btn-ghost btn-sm">Cancelar</button>
                    </form>
                    <form method="post" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="btn btn-error btn-sm text-white font-bold">Terminar sessão</button>
                    </form>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button>fechar</button>
            </form>
        </dialog>

        <dialog id="modal-confirmar-eliminar" class="modal">
            <div class="modal-box bg-base-100 border border-base-300 shadow-2xl max-w-md text-center p-6 flex flex-col items-center gap-3">
                <div class="text-4xl text-error">⚠️</div>
                <h3 class="font-bold text-lg text-amber-950">Confirmar Eliminação</h3>
                <p id="modal-confirmar-texto" class="text-xs opacity-80 leading-relaxed"></p>
                <div class="modal-action flex justify-center gap-3 mt-4 w-full">
                    <form method="dialog">
                        <button class="btn btn-ghost btn-sm">Cancelar</button>
                    </form>
                    <form id="form-confirmar-eliminar" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-error btn-sm text-white font-bold">Eliminar Definitivamente</button>
                    </form>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button>Fechar</button>
            </form>
        </dialog>

        <script>
            function confirmarEliminacao(botao) {
                document.getElementById('form-confirmar-eliminar').action = botao.dataset.url;
                document.getElementById('modal-confirmar-texto').textContent = 'Tem a certeza de que pretende eliminar ' + botao.dataset.nome + '? Esta ação é irreversível.';
                document.getElementById('modal-confirmar-eliminar').showModal();
            }
        </script>

        @stack('modals')
        @livewireScripts
    </body>
</html>
