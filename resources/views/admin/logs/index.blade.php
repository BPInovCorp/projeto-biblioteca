<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-100 p-6 rounded-2xl shadow-sm border border-base-300">
                <div>
                    <h2 class="font-semibold text-2xl text-base-content leading-tight">
                        {{ __('Logs de Auditoria') }}
                    </h2>
                    <p class="text-xs text-base-content/60 mt-1">
                        {{ __('Rastreio integral de ações dos utilizadores, registo de IP, navegador e histórico de alterações.') }}
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="badge badge-neutral text-white font-mono text-xs py-3 px-3 shadow-sm">
                        {{ $logs->total() }} {{ __('registos') }}
                    </span>

                    <div class="relative" x-data="{ aberto: false }">
                        <button type="button"
                                @click="aberto = !aberto"
                                class="btn btn-outline btn-sm font-bold text-xs gap-1 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>{{ __('Exportar') }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="aberto"
                             x-cloak
                             @click.outside="aberto = false"
                             class="absolute right-0 top-full mt-1 z-30 bg-base-100 rounded-2xl border border-base-300 shadow-xl w-44 p-2">
                            <ul class="menu menu-sm p-0 text-xs font-semibold">
                                <li>
                                    <a href="{{ route('admin.logs.export', array_merge(request()->query(), ['formato' => 'xlsx'])) }}" @click="aberto = false" class="flex items-center justify-between">
                                        <span>📊 {{ __('Excel') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.logs.export', array_merge(request()->query(), ['formato' => 'csv'])) }}" @click="aberto = false" class="flex items-center justify-between">
                                        <span>📄 {{ __('CSV') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-error text-white shadow-sm text-xs">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <div class="bg-base-100 p-4 rounded-2xl shadow-sm border border-base-300">
                <form method="GET" action="{{ route('admin.logs.index') }}" class="space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <div>
                            <label class="label py-1">
                                <span class="label-text text-xs font-bold uppercase tracking-wider text-base-content/70">{{ __('Módulo') }}</span>
                            </label>
                            <select name="modulo" class="select select-bordered select-sm w-full bg-base-100 text-xs">
                                <option value="todos">{{ __('Todos os Módulos') }}</option>
                                @foreach ($modulos as $mod)
                                    <option value="{{ $mod }}" {{ $moduloSelecionado === $mod ? 'selected' : '' }}>
                                        {{ $mod }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="label py-1">
                                <span class="label-text text-xs font-bold uppercase tracking-wider text-base-content/70">{{ __('Utilizador') }}</span>
                            </label>
                            <select name="user_id" class="select select-bordered select-sm w-full bg-base-100 text-xs">
                                <option value="todos">{{ __('Todos os Utilizadores') }}</option>
                                @foreach ($utilizadores as $u)
                                    <option value="{{ $u->id }}" {{ (string) $userSelecionado === (string) $u->id ? 'selected' : '' }}>
                                        {{ $u->name }} ({{ $u->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="label py-1">
                                <span class="label-text text-xs font-bold uppercase tracking-wider text-base-content/70">{{ __('Data Inicial') }}</span>
                            </label>
                            <input type="date"
                                   name="data_inicio"
                                   value="{{ $dataInicio }}"
                                   class="input input-bordered input-sm w-full bg-base-100 text-xs font-mono" />
                        </div>

                        <div>
                            <label class="label py-1">
                                <span class="label-text text-xs font-bold uppercase tracking-wider text-base-content/70">{{ __('Data Final') }}</span>
                            </label>
                            <input type="date"
                                   name="data_fim"
                                   value="{{ $dataFim }}"
                                   class="input input-bordered input-sm w-full bg-base-100 text-xs font-mono" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 items-end pt-1">
                        <div class="lg:col-span-8">
                            <label class="label py-1">
                                <span class="label-text text-xs font-bold uppercase tracking-wider text-base-content/70">{{ __('Pesquisa Livre') }}</span>
                            </label>
                            <div class="relative w-full">
                                <input type="text"
                                       name="search"
                                       value="{{ $pesquisa }}"
                                       placeholder="Ação, ID, IP..."
                                       class="input input-bordered input-sm w-full bg-base-100 text-xs pl-8" />
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute left-2.5 top-1/2 -translate-y-1/2 text-base-content/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0118 0z" />
                                </svg>
                            </div>
                        </div>

                        <div class="lg:col-span-4 flex items-center gap-2">
                            <button type="submit" class="btn btn-primary btn-sm grow font-bold text-xs shadow-sm">
                                {{ __('Filtrar Registos') }}
                            </button>
                            @if ($moduloSelecionado || $userSelecionado || $pesquisa || $dataInicio || $dataFim)
                                <a href="{{ route('admin.logs.index') }}" class="btn btn-ghost btn-sm text-xs">
                                    {{ __('Limpar') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            @if ($logs->isEmpty())
                <div class="card bg-base-100 shadow-sm border border-base-300 text-center py-16 px-4 rounded-2xl">
                    <div class="card-body items-center space-y-3">
                        <div class="w-16 h-16 rounded-full bg-base-200 flex items-center justify-center text-3xl text-base-content/40">
                            📜
                        </div>
                        <h3 class="text-xl font-bold text-base-content">
                            {{ __('Nenhum registo de log encontrado') }}
                        </h3>
                        <p class="text-xs text-base-content/60 max-w-md">
                            @if ($pesquisa || $moduloSelecionado || $userSelecionado || $dataInicio || $dataFim)
                                {{ __('Não foram encontrados registos para os filtros selecionados. Tente ajustar os parâmetros de pesquisa.') }}
                            @else
                                {{ __('Ainda não existem ações de auditoria registadas no sistema.') }}
                            @endif
                        </p>
                        @if ($pesquisa || $moduloSelecionado || $userSelecionado || $dataInicio || $dataFim)
                            <div class="card-actions pt-2">
                                <a href="{{ route('admin.logs.index') }}" class="btn btn-outline btn-sm font-bold text-xs">
                                    {{ __('Repor Filtros') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="card bg-base-100 shadow-sm border border-base-300 rounded-2xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <thead>
                                <tr class="bg-base-200/80 text-base-content/80 text-xs uppercase font-bold">
                                    <th class="py-3.5">{{ __('Data') }}</th>
                                    <th class="py-3.5">{{ __('Hora') }}</th>
                                    <th class="py-3.5">{{ __('User') }}</th>
                                    <th class="text-center py-3.5">{{ __('Módulo') }}</th>
                                    <th class="text-center py-3.5">{{ __('ID Objeto') }}</th>
                                    <th class="py-3.5">{{ __('Alteração') }}</th>
                                    <th class="py-3.5">{{ __('IP') }}</th>
                                    <th class="py-3.5">{{ __('Browser') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-base-200">
                                @foreach ($logs as $log)
                                    @php
                                        $badgeClass = match ($log->modulo) {
                                            'Requisições' => 'badge-accent text-accent-content font-bold',
                                            'Livros' => 'badge-info text-info-content font-bold',
                                            'Encomendas' => 'badge-primary text-primary-content font-bold',
                                            'Avaliações' => 'badge-warning text-stone-900 font-bold',
                                            'Sistema' => 'badge-neutral text-white font-bold',
                                            default => 'badge-ghost',
                                        };
                                    @endphp
                                    <tr class="hover">
                                        <td class="font-mono text-xs whitespace-nowrap text-base-content/80">
                                            {{ $log->data_formatada }}
                                        </td>
                                        <td class="font-mono text-xs whitespace-nowrap text-base-content/60">
                                            {{ $log->hora_formatada }}
                                        </td>
                                        <td class="whitespace-nowrap">
                                            @if ($log->user)
                                                <div class="font-bold text-xs text-base-content">
                                                    {{ $log->user->name }}
                                                </div>
                                                <div class="text-[11px] text-base-content/60 font-mono">
                                                    {{ $log->user->email }}
                                                </div>
                                            @else
                                                <span class="badge badge-ghost badge-sm text-[10px] italic">
                                                    {{ __('Sistema / Anónimo') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center whitespace-nowrap">
                                            <span class="badge {{ $badgeClass }} badge-sm text-[11px] py-2 px-2.5 shadow-sm">
                                                {{ $log->modulo }}
                                            </span>
                                        </td>
                                        <td class="text-center whitespace-nowrap font-mono text-xs text-base-content/70">
                                            {{ $log->objeto_id ? '#' . $log->objeto_id : '—' }}
                                        </td>
                                        <td class="text-xs text-base-content max-w-md leading-snug">
                                            {{ $log->alteracao }}
                                        </td>
                                        <td class="font-mono text-xs text-base-content/70 whitespace-nowrap">
                                            {{ $log->ip ?? '—' }}
                                        </td>
                                        <td class="text-xs text-base-content/80 whitespace-nowrap">
                                            <span class="inline-flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-base-content/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                                </svg>
                                                {{ $log->browser ?? __('Desconhecido') }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-center pt-2">
                    {{ $logs->links() }}
                </div>
            @endif

        </div>
    </div>
</x-app-layout>