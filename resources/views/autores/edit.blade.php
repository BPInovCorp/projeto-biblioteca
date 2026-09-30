<x-app-layout>
    <div data-theme="autumn" class="min-h-screen bg-base-200 text-base-content py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col gap-6">
            <div class="card bg-base-100 shadow-xl border border-base-300 p-6 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight text-amber-950">
                        Editar Autor ✍️
                    </h1>
                    <p class="text-xs opacity-80 mt-1">
                        Atualize os dados do autor literário.
                    </p>
                </div>
                <a href="{{ route('autores.index') }}" class="btn btn-outline btn-sm">
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

                <form action="{{ route('autores.update', $autor->id) }}" method="POST" class="flex flex-col gap-4">
                    @csrf
                    @method('PUT')

                    <div class="form-control w-full">
                        <label class="label pb-1" for="nome">
                            <span class="label-text font-semibold text-xs uppercase tracking-wider text-amber-950">Nome do Autor</span>
                        </label>
                        <input type="text" id="nome" name="nome" value="{{ old('nome', $autor->nome) }}" required class="input input-bordered w-full bg-base-100 text-base-content" />
                    </div>

                    <div class="form-control w-full">
                        <label class="label pb-1" for="foto">
                            <span class="label-text font-semibold text-xs uppercase tracking-wider text-amber-950">URL da Fotografia</span>
                        </label>
                        <input type="text" id="foto" name="foto" value="{{ old('foto', $autor->foto) }}" class="input input-bordered w-full bg-base-100 text-base-content" placeholder="https://..." />
                    </div>

                    <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-base-300">
                        <a href="{{ route('autores.index') }}" class="btn btn-ghost btn-sm">Cancelar</a>
                        <button type="submit" class="btn btn-primary btn-sm">Atualizar Autor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>