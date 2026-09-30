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

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-error btn-sm">
                        Terminar Sessão
                    </button>
                </form>
            </div>
        </header>

        <main class="grow">
            {{ $slot }}
        </main>

        <footer class="footer footer-center p-6 bg-base-100 text-base-content border-t border-base-300 mt-12">
            <div>
                <p class="text-sm font-medium">Projeto Biblioteca 2026 - Inovcorp - Desenvolvido por Bruno Pinto</p>
            </div>
        </footer>

        @stack('modals')
        @livewireScripts
    </body>
</html>
