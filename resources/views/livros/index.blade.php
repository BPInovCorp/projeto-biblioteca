<x-app-layout>
    <div data-theme="autumn" class="min-h-screen bg-base-200 text-base-content py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col gap-6">
            <div class="card bg-base-100 shadow-xl border border-base-300 p-6 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight text-amber-950">
                        Gestão de Livros 📖
                    </h1>
                    <p class="text-xs opacity-80 mt-1">
                        Consulte, pesquise, filtre por campo, ordene e exporte o catálogo para Excel.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('livros.export') }}" class="btn btn-success btn-sm text-white">
                        📥 Exportar Excel
                    </a>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline btn-sm">
                        Voltar ao Painel
                    </a>
                    <a href="{{ route('livros.create') }}" class="btn btn-primary btn-sm">
                        + Adicionar Livro
                    </a>
                </div>
            </div>

            <div class="card bg-base-100 shadow-xl border border-base-300 p-6">
                <form method="GET" action="{{ route('livros.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 items-end">
                    <div class="form-control w-full">
                        <label class="label p-0"><span class="label-text text-xs font-bold uppercase tracking-wider">Pesquisa</span></label>
                        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Nome ou ISBN..." class="input input-bordered input-sm w-full bg-base-100 text-base-content" />
                    </div>
                    
                    <div class="form-control w-full">
                        <label class="label p-0"><span class="label-text text-xs font-bold uppercase tracking-wider">Editora</span></label>
                        <select name="editora_id" class="select select-bordered select-sm w-full bg-base-100 text-base-content text-[10px] sm:text-xs">
                            <option value="">Todas as editoras</option>
                            @foreach($editoras as $editora)
                                <option value="{{ $editora->id }}" {{ ($editoraFiltro ?? '') == $editora->id ? 'selected' : '' }}>
                                    {{ $editora->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control w-full">
                        <label class="label p-0"><span class="label-text text-xs font-bold uppercase tracking-wider">Autor</span></label>
                        <select name="autor_id" class="select select-bordered select-sm w-full bg-base-100 text-base-content text-[10px] sm:text-xs">
                            <option value="">Todos os autores</option>
                            @foreach($autores as $autor)
                                <option value="{{ $autor->id }}" {{ ($autorFiltro ?? '') == $autor->id ? 'selected' : '' }}>
                                    {{ $autor->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm grow">Filtrar</button>
                        @if($search || $editoraFiltro || $autorFiltro)
                            <a href="{{ route('livros.index') }}" class="btn btn-ghost btn-sm">Limpar</a>
                        @endif
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr>
                                <th>
                                    <a href="{{ route('livros.index', ['sort' => 'id', 'direction' => $sort === 'id' && $direction === 'asc' ? 'desc' : 'asc', 'search' => $search, 'editora_id' => $editoraFiltro, 'autor_id' => $autorFiltro]) }}" class="flex items-center gap-1 hover:text-primary">
                                        ID @if($sort === 'id') <span>{{ $direction === 'asc' ? '▲' : '▼' }}</span> @endif
                                    </a>
                                </th>
                                <th>ISBN</th>
                                <th>
                                    <a href="{{ route('livros.index', ['sort' => 'nome', 'direction' => $sort === 'nome' && $direction === 'asc' ? 'desc' : 'asc', 'search' => $search, 'editora_id' => $editoraFiltro, 'autor_id' => $autorFiltro]) }}" class="flex items-center gap-1 hover:text-primary">
                                        Nome @if($sort === 'nome') <span>{{ $direction === 'asc' ? '▲' : '▼' }}</span> @endif
                                    </a>
                                </th>
                                <th>Editora</th>
                                <th>Autores</th>
                                <th>
                                    <a href="{{ route('livros.index', ['sort' => 'preco', 'direction' => $sort === 'preco' && $direction === 'asc' ? 'desc' : 'asc', 'search' => $search, 'editora_id' => $editoraFiltro, 'autor_id' => $autorFiltro]) }}" class="flex items-center gap-1 hover:text-primary">
                                        Preço @if($sort === 'preco') <span>{{ $direction === 'asc' ? '▲' : '▼' }}</span> @endif
                                    </a>
                                </th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($livrosPaginados as $livro)
                                <tr>
                                    <th class="font-mono">{{ $livro->id }}</th>
                                    <td class="font-mono text-xs">{{ $livro->isbn }}</td>
                                    <td class="font-semibold">{{ $livro->nome }}</td>
                                    <td>{{ $livro->editora->nome ?? '' }}</td>
                                    <td>{{ $livro->autores->pluck('nome')->implode(', ') }}</td>
                                    <td class="font-bold text-primary">{{ number_format($livro->preco, 2, ',', '.') }} €</td>
                                    <td class="text-end">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('livros.edit', $livro->id) }}" class="btn btn-xs btn-outline">Editar</a>
                                                <button type="button" class="btn btn-xs btn-outline btn-error" data-url="{{ route('livros.destroy', $livro->id) }}" data-nome="o livro {{ $livro->nome }}" onclick="confirmarEliminacao(this)">Eliminar</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-8 opacity-60">
                                        Nenhum livro encontrado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $livrosPaginados->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>