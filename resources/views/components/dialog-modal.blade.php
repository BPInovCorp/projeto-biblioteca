@props(['id' => null, 'maxWidth' => null])

<x-modal :id="$id" :maxWidth="$maxWidth" {{ $attributes }}>
    <div class="px-6 py-4 bg-base-100 text-base-content">
        <div class="text-lg font-bold text-amber-950">
            {{ $title }}
        </div>

        <div class="mt-4 text-sm text-base-content opacity-90">
            {{ $content }}
        </div>
    </div>

    <div class="flex flex-row justify-end px-6 py-4 bg-base-200 border-t border-base-300 text-end">
        {{ $footer }}
    </div>
</x-modal>
