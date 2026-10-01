<x-app-layout>
    <div data-theme="autumn" class="min-h-screen bg-base-200 text-base-content py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col gap-8">
            <div class="card bg-base-100 shadow-xl border border-base-300 p-6 sm:p-8">
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-amber-950">
                    Olá, {{ auth()->user()->name }}! 👋
                </h1>
                <p class="text-sm opacity-80 mt-1">
                    Bem-vindo ao painel de controlo da Biblioteca.
                </p>
            </div>

            <div class="stats stats-vertical lg:stats-horizontal shadow-xl bg-base-100 border border-base-300 w-full">
                <div class="stat">
                    <div class="stat-figure text-primary text-3xl">📖</div>
                    <div class="stat-title font-semibold text-xs uppercase tracking-wider">Total de Livros</div>
                    <div class="stat-value text-primary font-serif font-black">{{ \App\Models\Livro::count() }}</div>
                    <div class="stat-desc text-xs opacity-70">Obras do arquivo digital</div>
                </div>

                <div class="stat">
                    <div class="stat-figure text-secondary text-3xl">✍️</div>
                    <div class="stat-title font-semibold text-xs uppercase tracking-wider">Autores Registados</div>
                    <div class="stat-value text-secondary font-serif font-black">{{ \App\Models\Autor::count() }}</div>
                    <div class="stat-desc text-xs opacity-70">Escritores catalogados</div>
                </div>

                <div class="stat">
                    <div class="stat-figure text-accent text-3xl">🏢</div>
                    <div class="stat-title font-semibold text-xs uppercase tracking-wider">Editoras Parceiras</div>
                    <div class="stat-value text-accent font-serif font-black">{{ \App\Models\Editora::count() }}</div>
                    <div class="stat-desc text-xs opacity-70">Certificações editoriais</div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="card bg-base-100 shadow-xl border border-base-300 hover:shadow-2xl transition-all duration-300">
                    <div class="card-body p-6 flex flex-col justify-between">
                        <div>
                            <div class="text-3xl mb-2">📖</div>
                            <h2 class="card-title text-xl font-bold">Livros</h2>
                            <p class="text-xs opacity-80 mt-1">
                                Catálogo de obras, pesquisa avançada por ISBN, ordenação e exportação para Excel.
                            </p>
                        </div>
                        <div class="card-actions justify-end mt-6">
                            <a href="{{ route('livros.index') }}" class="btn btn-primary btn-sm w-full">
                                Gerir Livros
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card bg-base-100 shadow-xl border border-base-300 hover:shadow-2xl transition-all duration-300">
                    <div class="card-body p-6 flex flex-col justify-between">
                        <div>
                            <div class="text-3xl mb-2">✍️</div>
                            <h2 class="card-title text-xl font-bold">Autores</h2>
                            <p class="text-xs opacity-80 mt-1">
                                Listagem de autores, consulta de biografias, fotografias e obras associadas.
                            </p>
                        </div>
                        <div class="card-actions justify-end mt-6">
                            <a href="{{ route('autores.index') }}" class="btn btn-primary btn-sm w-full">
                                Gerir Autores
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card bg-base-100 shadow-xl border border-base-300 hover:shadow-2xl transition-all duration-300">
                    <div class="card-body p-6 flex flex-col justify-between">
                        <div>
                            <div class="text-3xl mb-2">🏢</div>
                            <h2 class="card-title text-xl font-bold">Editoras</h2>
                            <p class="text-xs opacity-80 mt-1">
                                Diretório de editoras parceiras, gestão de contactos e consulta de logótipos.
                            </p>
                        </div>
                        <div class="card-actions justify-end mt-6">
                            <a href="{{ route('editoras.index') }}" class="btn btn-primary btn-sm w-full">
                                Gerir Editoras
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
