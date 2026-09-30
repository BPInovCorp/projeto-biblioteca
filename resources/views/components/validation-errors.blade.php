@if ($errors->any())
    <div class="mb-4 bg-error text-white p-4 rounded-lg shadow-md flex flex-col gap-2 w-full">
        <div class="font-bold text-sm text-white flex items-center gap-2">
            <span>⚠️</span> {{ __('Atenção! Por favor, verifique os seguintes erros:') }}
        </div>

        <ul class="list-disc list-inside text-xs text-white space-y-1 pl-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
