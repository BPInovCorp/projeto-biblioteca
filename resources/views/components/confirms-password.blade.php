@props(['title' => __('Confirmar Palavra-passe 🔐'), 'content' => __('Por motivos de segurança, introduza a sua palavra-passe para continuar.'), 'button' => __('Confirmar')])

@php
    $confirmableId = md5($attributes->wire('then'));
@endphp

<span
    {{ $attributes->wire('then') }}
    x-data
    x-ref="span"
    x-on:click="$wire.startConfirmingPassword('{{ $confirmableId }}')"
    x-on:password-confirmed.window="setTimeout(() => $event.detail.id === '{{ $confirmableId }}' && $refs.span.dispatchEvent(new CustomEvent('then', { bubbles: false })), 250);"
>
    {{ $slot }}
</span>

@once
<x-dialog-modal wire:model.live="confirmingPassword">
    <x-slot name="title">
        <span class="text-amber-950 font-bold text-lg">{{ $title }}</span>
    </x-slot>

    <x-slot name="content">
        <p class="text-xs text-base-content opacity-80 leading-relaxed mb-4">
            {{ $content }}
        </p>

        <div x-data="{}" x-on:confirming-password.window="setTimeout(() => $refs.confirmable_password.focus(), 250)">
            <input type="password" class="input input-bordered w-full bg-base-100 text-base-content text-sm" placeholder="A sua palavra-passe" autocomplete="current-password"
                        x-ref="confirmable_password"
                        wire:model="confirmablePassword"
                        wire:keydown.enter="confirmPassword" />

            <x-input-error for="confirmable_password" class="mt-2 text-xs text-error font-medium" />
        </div>
    </x-slot>

    <x-slot name="footer">
        <button type="button" class="btn btn-ghost btn-sm" wire:click="stopConfirmingPassword" wire:loading.attr="disabled">
            {{ __('Cancelar') }}
        </button>

        <button type="button" class="btn btn-primary btn-sm ms-3 font-bold" dusk="confirm-password-button" wire:click="confirmPassword" wire:loading.attr="disabled">
            {{ $button }}
        </button>
    </x-slot>
</x-dialog-modal>
@endonce