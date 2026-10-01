<x-app-layout>
    <div data-theme="autumn" class="min-h-screen bg-base-200 text-base-content py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col gap-6">
            <div class="card bg-base-100 shadow-xl border border-base-300 p-6 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight text-amber-950">
                        Gestão de Autores ✍️
                    </h1>
                    <p class="text-xs opacity-80 mt-1">
                        Consulte, pesquise, ordene e gira os autores literários da biblioteca.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline btn-sm">
                        Voltar ao Painel
                    </a>
                    <a href="{{ route('autores.create') }}" class="btn btn-primary btn-sm">
                        + Adicionar Autor
                    </a>
                </div>
            </div>

            <div class="card bg-base-100 shadow-xl border border-base-300 p-6">
                <form method="GET" action="{{ route('autores.index') }}" class="flex flex-col sm:flex-row gap-3 mb-6">
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Pesquisar autor por nome..." class="input input-bordered input-sm w-full sm:max-w-xs bg-base-100 text-base-content" />
                    <button type="submit" class="btn btn-primary btn-sm">Pesquisar</button>
                    @if($search)
                        <a href="{{ route('autores.index') }}" class="btn btn-ghost btn-sm">Limpar</a>
                    @endif
                </form>

                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr>
                                <th>
                                    <a href="{{ route('autores.index', ['sort' => 'id', 'direction' => $sort === 'id' && $direction === 'asc' ? 'desc' : 'asc', 'search' => $search]) }}" class="flex items-center gap-1 hover:text-primary">
                                        ID @if($sort === 'id') <span>{{ $direction === 'asc' ? '▲' : '▼' }}</span> @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('autores.index', ['sort' => 'nome', 'direction' => $sort === 'nome' && $direction === 'asc' ? 'desc' : 'asc', 'search' => $search]) }}" class="flex items-center gap-1 hover:text-primary">
                                        Nome @if($sort === 'nome') <span>{{ $direction === 'asc' ? '▲' : '▼' }}</span> @endif
                                    </a>
                                </th>
                                <th>Foto</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($autoresPaginados as $autor)
                                <tr>
                                    <th class="font-mono">{{ $autor->id }}</th>
                                    <td class="font-semibold">{{ $autor->nome }}</td>
                                    <td>
                                        @if($autor->foto)
                                            <span class="text-xs font-mono opacity-70 truncate max-w-xs block">{{ $autor->foto }}</span>
                                        @else
                                            <span class="text-xs opacity-50">Sem foto</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('autores.edit', $autor->id) }}" class="btn btn-xs btn-outline">Editar</a>
                                                <button type="button" class="btn btn-xs btn-outline btn-error" data-url="{{ route('autores.destroy', $autor->id) }}" data-nome="o autor {{ $autor->nome }}" onclick="confirmarEliminacao(this)">Eliminar</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-8 opacity-60">
                                        Nenhum autor encontrado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $autoresPaginados->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>