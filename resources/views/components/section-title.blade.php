<div class="md:col-span-1 flex justify-between">
    <div class="px-4 sm:px-0">
        <h3 class="text-lg font-bold text-amber-950">{{ $title }}</h3>

        <p class="mt-1 text-xs text-base-content opacity-80 leading-relaxed">
            {{ $description }}
        </p>
    </div>

    <div class="px-4 sm:px-0">
        {{ $aside ?? '' }}
    </div>
</div>
