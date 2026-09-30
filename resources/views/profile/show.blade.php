<x-app-layout>
    <div data-theme="autumn" class="min-h-screen bg-base-200 text-base-content py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col gap-8">
            <div class="card bg-base-100 shadow-xl border border-base-300 p-6 sm:p-8">
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-amber-950">
                    O Meu Perfil & Segurança ⚙️
                </h1>
                <p class="text-sm opacity-80 mt-1">
                    Gerencie os dados da sua conta, palavra-passe e configure a Autenticação de 2 Fatores (2FA).
                </p>
            </div>

            @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                <div class="card bg-base-100 shadow-xl border border-base-300 p-6 sm:p-8">
                    @livewire('profile.update-profile-information-form')
                </div>
            @endif

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                <div class="card bg-base-100 shadow-xl border border-base-300 p-6 sm:p-8">
                    @livewire('profile.update-password-form')
                </div>
            @endif

            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <div class="card bg-base-100 shadow-xl border border-base-300 p-6 sm:p-8">
                    @livewire('profile.two-factor-authentication-form')
                </div>
            @endif

            <div class="card bg-base-100 shadow-xl border border-base-300 p-6 sm:p-8">
                @livewire('profile.logout-other-browser-sessions-form')
            </div>

            @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
                <div class="card bg-base-100 shadow-xl border border-error/30 p-6 sm:p-8">
                    @livewire('profile.delete-user-form')
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
