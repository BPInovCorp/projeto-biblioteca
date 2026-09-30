<x-guest-layout>
    <div data-theme="autumn" class="min-h-screen bg-base-200 text-base-content flex flex-col justify-center items-center p-4">
        <div class="mb-6 text-center">
            <a href="{{ url('/') }}" class="text-3xl font-extrabold tracking-tight flex items-center gap-2 text-amber-950 hover:opacity-90 transition-opacity">
                <span>📚</span> {{ config('app.name', 'Biblioteca') }}
            </a>
        </div>

        <div class="card bg-base-100 shadow-2xl border border-base-300 w-full max-w-md p-6 sm:p-8">
            <h2 class="text-2xl font-bold text-center tracking-tight mb-1">Recuperar Palavra-passe</h2>
            <p class="text-xs text-center opacity-75 mb-6">Redefinição de credenciais de acesso</p>

            <div class="mb-4 text-xs text-base-content opacity-80 leading-relaxed text-justify">
                {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
            </div>

            @session('status')
                <div class="mb-4 alert alert-success text-xs">
                    {{ $value }}
                </div>
            @endsession

            <x-validation-errors class="mb-4 bg-red-600 text-white p-4 rounded-lg shadow-md flex flex-col gap-2 w-full text-xs" />

            <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-4">
                @csrf

                <div class="form-control w-full">
                    <label class="label pb-1" for="email">
                        <span class="label-text font-semibold text-xs uppercase tracking-wider text-amber-950">Endereço de Email</span>
                    </label>
                    <input id="email" class="input input-bordered w-full text-sm" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="exemplo@biblioteca.pt" />
                </div>

                <div class="flex items-center justify-between mt-4 pt-4 border-t border-base-300">
                    <a href="{{ route('login') }}" class="text-xs text-primary hover:underline font-semibold">
                        Voltar ao Login
                    </a>

                    <button type="submit" class="btn btn-primary btn-sm shadow-md font-bold">
                        {{ __('Email Password Reset Link') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>