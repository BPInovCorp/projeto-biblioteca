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
                <a href="{{ url('/') }}" class="btn btn-ghost normal-case text-2xl font-bold tracking-wide">
                    📚 {{ config('app.name', 'Biblioteca') }}
                </a>
            </div>

            <div class="navbar-end gap-2 sm:gap-3">
                <a href="{{ url('/') }}" class="btn btn-ghost btn-sm">
                    Estante
                </a>

                @if(request()->user()?->isAdmin())
                    <a href="{{ route('dashboard') }}" class="btn btn-ghost btn-sm">
                        Painel de Controlo
                    </a>
                @endif

                <a href="{{ route('profile.show') }}" class="btn btn-primary btn-sm">
                    O meu Perfil
                </a>

                <button type="button" class="btn btn-outline btn-error btn-sm" onclick="document.getElementById('modal-confirmar-logout').showModal()">
                    Terminar Sessão
                </button>
            </div>
        </header>

        @if (session('success'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
                <div class="alert alert-success text-white shadow-md flex items-center gap-2 text-xs sm:text-sm font-bold">
                    <span>✅</span> {{ session('success') }}
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
