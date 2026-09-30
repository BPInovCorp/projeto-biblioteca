@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-bold text-xs text-amber-950 uppercase tracking-wider mb-1']) }}>
    {{ $value ?? $slot }}
</label>