<x-guest-layout>
    <div data-theme="autumn" class="min-h-screen bg-base-200 text-base-content flex flex-col justify-center items-center p-4">
        <div class="mb-6 text-center">
            <a href="{{ url('/') }}" class="text-3xl font-extrabold tracking-tight flex items-center gap-2 text-amber-950 hover:opacity-90 transition-opacity">
                <span>📚</span> {{ config('app.name', 'Biblioteca') }}
            </a>
        </div>

        <div class="card bg-base-100 shadow-2xl border border-base-300 w-full max-w-md p-6 sm:p-8">
            <h2 class="text-2xl font-bold text-center tracking-tight mb-1">Criar Conta de Leitor</h2>
            <p class="text-xs text-center opacity-75 mb-6">Registe-se para aceder ao catálogo completo da biblioteca</p>

            <x-validation-errors class="mb-4 alert alert-error text-xs" />

            <form method="POST" action="{{ route('register') }}" class="flex flex-col gap-4">
                @csrf

                <div class="form-control w-full">
                    <label class="label pb-1" for="name">
                        <span class="label-text font-semibold text-xs">Nome Completo</span>
                    </label>
                    <input id="name" class="input input-bordered w-full text-sm" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="O seu nome" />
                </div>

                <div class="form-control w-full">
                    <label class="label pb-1" for="email">
                        <span class="label-text font-semibold text-xs">Endereço de Email</span>
                    </label>
                    <input id="email" class="input input-bordered w-full text-sm" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="exemplo@biblioteca.pt" />
                </div>

                <div class="form-control w-full">
                    <label class="label pb-1" for="password">
                        <span class="label-text font-semibold text-xs">Palavra-passe</span>
                    </label>
                    <input id="password" class="input input-bordered w-full text-sm" type="password" name="password" required autocomplete="new-password" placeholder="••••••••" />
                </div>

                <div class="form-control w-full">
                    <label class="label pb-1" for="password_confirmation">
                        <span class="label-text font-semibold text-xs">Confirmar Palavra-passe</span>
                    </label>
                    <input id="password_confirmation" class="input input-bordered w-full text-sm" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" />
                </div>

                @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                    <div class="form-control mt-1">
                        <label for="terms" class="label cursor-pointer justify-start gap-3 p-0">
                            <input type="checkbox" name="terms" id="terms" required class="checkbox checkbox-primary checkbox-sm" />
                            <span class="label-text text-xs opacity-80">
                                {!! __('Concordo com os :terms_of_service e :privacy_policy', [
                                        'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="text-primary hover:underline">'.__('Termos de Serviço').'</a>',
                                        'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'" class="text-primary hover:underline">'.__('Política de Privacidade').'</a>',
                                ]) !!}
                            </span>
                        </label>
                    </div>
                @endif

                <div class="mt-2">
                    <button type="submit" class="btn btn-primary w-full shadow-md font-bold">
                        Registar
                    </button>
                </div>

                <div class="text-center mt-3 pt-4 border-t border-base-300">
                    <a href="{{ route('login') }}" class="text-xs text-primary hover:underline font-semibold">
                        Já tem conta? Entre aqui!
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>