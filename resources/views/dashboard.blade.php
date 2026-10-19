<x-app-layout>
    <div data-theme="autumn" class="min-h-screen bg-base-200 text-base-content py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col gap-8">
            @if (session('status'))
                <div class="alert alert-success text-white shadow-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <div class="card bg-base-100 shadow-xl border border-base-300 p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-amber-950">
                        Olá, {{ auth()->user()->name }}! 👋
                    </h1>
                    <p class="text-sm opacity-80 mt-1">
                        Bem-vindo ao painel de controlo da Biblioteca.
                    </p>
                </div>
                <div>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                        + Novo Administrador
                    </a>
                </div>
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

                <div class="stat">
                    <div class="stat-figure text-warning text-3xl">⏳</div>
                    <div class="stat-title font-semibold text-xs uppercase tracking-wider">{{ __('Avaliações Pendentes') }}</div>
                    <div class="stat-value text-warning font-serif font-black">{{ \App\Models\Comentario::where('status', 'suspenso')->count() }}</div>
                    <div class="stat-desc text-xs opacity-70">{{ __('A aguardar moderação') }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
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

                <div class="card bg-base-100 shadow-xl border border-base-300 hover:shadow-2xl transition-all duration-300">
                    <div class="card-body p-6 flex flex-col justify-between">
                        <div>
                            <div class="text-3xl mb-2">⭐</div>
                            <h2 class="card-title text-xl font-bold">{{ __('Avaliações') }}</h2>
                            <p class="text-xs opacity-80 mt-1">
                                {{ __('Triagem, aprovação e recusa justificada de críticas literárias dos cidadãos.') }}
                            </p>
                        </div>
                        <div class="card-actions justify-end mt-6">
                            <a href="{{ route('admin.comentarios.index') }}" class="btn btn-primary btn-sm w-full">
                                {{ __('Moderar Avaliações') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
