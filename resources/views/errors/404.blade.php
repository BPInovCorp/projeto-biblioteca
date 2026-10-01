<!DOCTYPE html>
<html lang="pt" data-theme="autumn">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Página Não Encontrada - {{ config('app.name', 'Biblioteca') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-base-200 text-base-content flex flex-col justify-center items-center p-4">
        <div class="card bg-base-100 shadow-2xl border border-base-300 w-full max-w-lg p-8 text-center flex flex-col items-center gap-4">
            <div class="text-6xl mb-2">📖</div>
            <h1 class="text-3xl font-extrabold tracking-tight text-amber-950">Página Não Encontrada</h1>
            <p class="text-sm opacity-80 leading-relaxed max-w-md">
                A página ou recurso que procura não existe no catálogo da biblioteca.
            </p>
            <div class="mt-4 pt-4 border-t border-base-300 w-full flex justify-center">
                <a href="{{ url('/') }}" class="btn btn-primary btn-sm">
                    Voltar à Estante
                </a>
            </div>
        </div>
        <p class="text-xs opacity-60 mt-8 font-mono">Erro 404 - Recurso Não Encontrado</p>
    </body>
</html>