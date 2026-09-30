<x-guest-layout>
    <div data-theme="autumn" class="min-h-screen bg-base-200 text-base-content flex flex-col justify-center items-center p-4">
        <div class="mb-6 text-center">
            <a href="{{ url('/') }}" class="text-3xl font-extrabold tracking-tight flex items-center gap-2 text-amber-950 hover:opacity-90 transition-opacity">
                <span>📚</span> {{ config('app.name', 'Biblioteca') }}
            </a>
        </div>

        <div class="card bg-base-100 shadow-2xl border border-base-300 w-full max-w-md p-6 sm:p-8">
            <h2 class="text-2xl font-bold text-center tracking-tight mb-1">Entrar na Conta</h2>
            <p class="text-xs text-center opacity-75 mb-6">Aceda ao sistema da biblioteca com as suas credenciais</p>

            <x-validation-errors class="mb-4 alert alert-error text-xs" />

            @session('status')
                <div class="mb-4 alert alert-success text-xs">
                    {{ $value }}
                </div>
            @endsession

            <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-4">
                @csrf

                <div class="form-control w-full">
                    <label class="label pb-1" for="email">
                        <span class="label-text font-semibold text-xs">Endereço de Email</span>
                    </label>
                    <input id="email" class="input input-bordered w-full text-sm" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="exemplo@biblioteca.pt" />
                </div>

                <div class="form-control w-full">
                    <div class="flex justify-between items-center pb-1">
                        <label class="label p-0" for="password">
                            <span class="label-text font-semibold text-xs">Palavra-passe</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a class="text-xs text-primary hover:underline font-medium" href="{{ route('password.request') }}">
                                Esqueceu a palavra-passe?
                            </a>
                        @endif
                    </div>
                    <input id="password" class="input input-bordered w-full text-sm" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
                </div>

                <div class="form-control mt-1">
                    <label for="remember_me" class="label cursor-pointer justify-start gap-3 p-0">
                        <input type="checkbox" id="remember_me" name="remember" class="checkbox checkbox-primary checkbox-sm" />
                        <span class="label-text text-xs opacity-80">Lembrar-me neste dispositivo</span>
                    </label>
                </div>

                <div class="mt-2">
                    <button type="submit" class="btn btn-primary w-full shadow-md font-bold">
                        Entrar
                    </button>
                </div>

                @if (Route::has('register'))
                    <div class="text-center mt-3 pt-4 border-t border-base-300">
                        <a href="{{ route('register') }}" class="text-xs text-primary hover:underline font-semibold">
                            Não tem conta? Crie uma aqui!
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>
</x-guest-layout>