<x-guest-layout>
    <div data-theme="autumn" class="min-h-screen bg-base-200 text-base-content flex flex-col justify-center items-center p-4">
        <div class="mb-6 text-center">
            <a href="{{ url('/') }}" class="text-3xl font-extrabold tracking-tight flex items-center gap-2 text-amber-950 hover:opacity-90 transition-opacity">
                <span>📚</span> {{ config('app.name', 'Biblioteca') }}
            </a>
        </div>

        <div class="card bg-base-100 shadow-2xl border border-base-300 w-full max-w-md p-6 sm:p-8" x-data="{ recovery: false }">
            <h2 class="text-2xl font-bold text-center tracking-tight mb-1">Verificação 2FA</h2>
            <p class="text-xs text-center opacity-75 mb-6">Autenticação de Dois Fatores</p>

            <div class="mb-4 text-xs text-base-content opacity-80 leading-relaxed" x-show="! recovery">
                {{ __('Please confirm access to your account by entering the authentication code provided by your authenticator application.') }}
            </div>

            <div class="mb-4 text-xs text-base-content opacity-80 leading-relaxed" x-cloak x-show="recovery">
                {{ __('Please confirm access to your account by entering one of your emergency recovery codes.') }}
            </div>

            <x-validation-errors class="mb-4 bg-red-600 text-white p-4 rounded-lg shadow-md flex flex-col gap-2 w-full text-xs" />

            <form method="POST" action="{{ route('two-factor.login') }}" class="flex flex-col gap-4">
                @csrf

                <div class="form-control w-full" x-show="! recovery">
                    <label class="label pb-1" for="code">
                        <span class="label-text font-semibold text-xs uppercase tracking-wider text-amber-950">Código de Autenticação</span>
                    </label>
                    <input id="code" class="input input-bordered w-full text-sm font-mono" type="text" inputmode="numeric" name="code" autofocus x-ref="code" autocomplete="one-time-code" placeholder="000000" />
                </div>

                <div class="form-control w-full" x-cloak x-show="recovery">
                    <label class="label pb-1" for="recovery_code">
                        <span class="label-text font-semibold text-xs uppercase tracking-wider text-amber-950">Código de Recuperação</span>
                    </label>
                    <input id="recovery_code" class="input input-bordered w-full text-sm font-mono" type="text" name="recovery_code" x-ref="recovery_code" autocomplete="one-time-code" placeholder="xxxx-xxxx" />
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-4 pt-4 border-t border-base-300">
                    <button type="button" class="text-xs text-primary hover:underline font-semibold cursor-pointer"
                                    x-show="! recovery"
                                    x-on:click="
                                        recovery = true;
                                        $nextTick(() => { $refs.recovery_code.focus() })
                                    ">
                        {{ __('Use a recovery code') }}
                    </button>

                    <button type="button" class="text-xs text-primary hover:underline font-semibold cursor-pointer"
                                    x-cloak
                                    x-show="recovery"
                                    x-on:click="
                                        recovery = false;
                                        $nextTick(() => { $refs.code.focus() })
                                    ">
                        {{ __('Use an authentication code') }}
                    </button>

                    <button type="submit" class="btn btn-primary btn-sm shadow-md font-bold w-full sm:w-auto">
                        {{ __('Log in') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>