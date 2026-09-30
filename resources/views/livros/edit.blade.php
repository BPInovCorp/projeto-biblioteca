<x-app-layout>
    <div data-theme="autumn" class="min-h-screen bg-base-200 text-base-content py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col gap-6">
            <div class="card bg-base-100 shadow-xl border border-base-300 p-6 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight text-amber-950">
                        Editar Livro 📖
                    </h1>
                    <p class="text-xs opacity-80 mt-1">
                        Atualize os dados da obra literária.
                    </p>
                </div>
                <a href="{{ route('livros.index') }}" class="btn btn-outline btn-sm">
                    Voltar
                </a>
            </div>

            <div class="card bg-base-100 shadow-xl border border-base-300 p-6 sm:p-8">
                @if ($errors->any())
                    <div class="mb-4 bg-error text-white p-4 rounded-lg shadow-md flex flex-col gap-2 w-full">
                        <div class="font-bold text-sm text-white flex items-center gap-2">
                            <span>⚠️</span> Por favor, corrija os erros abaixo:
                        </div>
                        <ul class="list-disc list-inside text-xs text-white space-y-1 pl-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('livros.update', $livro->id) }}" method="POST" class="flex flex-col gap-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control w-full">
                            <label class="label pb-1" for="isbn">
                                <span class="label-text font-semibold text-xs uppercase tracking-wider text-amber-950">ISBN</span>
                            </label>
                            <input type="text" id="isbn" name="isbn" value="{{ old('isbn', $livro->isbn) }}" required class="input input-bordered w-full bg-base-100 text-base-content" />
                        </div>

                        <div class="form-control w-full">
                            <label class="label pb-1" for="preco">
                                <span class="label-text font-semibold text-xs uppercase tracking-wider text-amber-950">Preço (€)</span>
                            </label>
                            <input type="number" step="0.01" id="preco" name="preco" value="{{ old('preco', $livro->preco) }}" required class="input input-bordered w-full bg-base-100 text-base-content" />
                        </div>
                    </div>

                    <div class="form-control w-full">
                        <label class="label pb-1" for="nome">
                            <span class="label-text font-semibold text-xs uppercase tracking-wider text-amber-950">Nome do Livro</span>
                        </label>
                        <input type="text" id="nome" name="nome" value="{{ old('nome', $livro->nome) }}" required class="input input-bordered w-full bg-base-100 text-base-content" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control w-full">
                            <label class="label pb-1" for="editora_id">
                                <span class="label-text font-semibold text-xs uppercase tracking-wider text-amber-950">Editora</span>
                            </label>
                            <select id="editora_id" name="editora_id" required class="select select-bordered w-full bg-base-100 text-base-content">
                                <option value="">Selecione a editora...</option>
                                @foreach($editoras as $editora)
                                    <option value="{{ $editora->id }}" {{ old('editora_id', $livro->editora_id) == $editora->id ? 'selected' : '' }}>
                                        {{ $editora->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-control w-full">
                            <label class="label pb-1" for="imagem_capa">
                                <span class="label-text font-semibold text-xs uppercase tracking-wider text-amber-950">URL da Imagem da Capa</span>
                            </label>
                            <input type="text" id="imagem_capa" name="imagem_capa" value="{{ old('imagem_capa', $livro->imagem_capa) }}" class="input input-bordered w-full bg-base-100 text-base-content" placeholder="https://..." />
                        </div>
                    </div>

                    <div class="form-control w-full">
                        <label class="label pb-1">
                            <span class="label-text font-semibold text-xs uppercase tracking-wider text-amber-950">Autores (Pode selecionar mais do que um)</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-3 border border-base-300 rounded-lg bg-base-100 max-h-40 overflow-y-auto">
                            @foreach($autores as $autor)
                                <label class="label cursor-pointer justify-start gap-3 p-1">
                                    <input type="checkbox" name="autores[]" value="{{ $autor->id }}" {{ in_array($autor->id, old('autores', $livro->autores->pluck('id')->toArray())) ? 'checked' : '' }} class="checkbox checkbox-primary checkbox-sm" />
                                    <span class="label-text text-xs">{{ $autor->nome }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-control w-full">
                        <label class="label pb-1" for="bibliografia">
                            <span class="label-text font-semibold text-xs uppercase tracking-wider text-amber-950">Bibliografia & Sinopse</span>
                        </label>
                        <textarea id="bibliografia" name="bibliografia" rows="4" class="textarea textarea-bordered w-full bg-base-100 text-base-content">{{ old('bibliografia', $livro->bibliografia) }}</textarea>
                    </div>

                    <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-base-300">
                        <a href="{{ route('livros.index') }}" class="btn btn-ghost btn-sm">Cancelar</a>
                        <button type="submit" class="btn btn-primary btn-sm">Atualizar Livro</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>