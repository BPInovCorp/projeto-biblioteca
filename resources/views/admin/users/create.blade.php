<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-base-content leading-tight">
                {{ __('Criar Novo Administrador') }}
            </h2>
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-ghost">
                {{ __('Voltar ao Painel') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="alert alert-success text-white shadow-lg mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <x-validation-errors class="mb-6" />

            <div class="card bg-base-100 shadow-xl border border-base-200">
                <div class="card-body">
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-base-content">{{ __('Dados do Novo Administrador') }}</h3>
                        <p class="text-sm text-base-content/70">{{ __('Preencha os campos abaixo para registar um novo utilizador com privilégios de administração.') }}</p>
                    </div>

                    <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
                        @csrf

                        <div class="form-control w-full">
                            <label class="label" for="name">
                                <span class="label-text font-medium text-base-content">{{ __('Nome Completo') }}</span>
                            </label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus class="input input-bordered w-full" placeholder="{{ __('ex: Maria Santos') }}" />
                        </div>

                        <div class="form-control w-full">
                            <label class="label" for="email">
                                <span class="label-text font-medium text-base-content">{{ __('Endereço de Email') }}</span>
                            </label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required class="input input-bordered w-full" placeholder="{{ __('ex: admin@biblioteca.pt') }}" />
                        </div>

                        <div class="form-control w-full">
                            <label class="label" for="password">
                                <span class="label-text font-medium text-base-content">{{ __('Palavra-passe') }}</span>
                            </label>
                            <input type="password" id="password" name="password" required autocomplete="new-password" class="input input-bordered w-full" />
                        </div>

                        <div class="form-control w-full">
                            <label class="label" for="password_confirmation">
                                <span class="label-text font-medium text-base-content">{{ __('Confirmar Palavra-passe') }}</span>
                            </label>
                            <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password" class="input input-bordered w-full" />
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-base-200">
                            <a href="{{ route('dashboard') }}" class="btn btn-ghost">
                                {{ __('Cancelar') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                {{ __('Criar Administrador') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>