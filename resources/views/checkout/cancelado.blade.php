<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-base-content leading-tight">
            {{ __('Processo de Pagamento') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="w-full bg-base-100 p-6 rounded-2xl shadow-sm border border-base-300">
                <div class="flex items-center justify-between max-w-2xl mx-auto">
                    <a href="{{ route('carrinho.index') }}" class="flex items-center gap-3 hover:opacity-80 transition-opacity">
                        <div class="w-10 h-10 rounded-full bg-primary/20 text-primary flex items-center justify-center font-bold text-sm">
                            ✓
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-wider font-semibold text-primary/80">{{ __('Passo 1') }}</div>
                            <div class="text-sm font-semibold text-base-content">{{ __('Carrinho') }}</div>
                        </div>
                    </a>

                    <div class="h-0.5 flex-1 bg-primary mx-4 sm:mx-6"></div>

                    <a href="{{ route('checkout.morada') }}" class="flex items-center gap-3 hover:opacity-80 transition-opacity">
                        <div class="w-10 h-10 rounded-full bg-primary/20 text-primary flex items-center justify-center font-bold text-sm">
                            ✓
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-wider font-semibold text-primary/80">{{ __('Passo 2') }}</div>
                            <div class="text-sm font-semibold text-base-content">{{ __('Morada de Entrega') }}</div>
                        </div>
                    </a>

                    <div class="h-0.5 flex-1 bg-error/40 mx-4 sm:mx-6"></div>

                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-error text-white flex items-center justify-center font-bold text-sm shadow">
                            ✕
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-wider font-bold text-error">{{ __('Passo 3') }}</div>
                            <div class="text-sm font-bold text-base-content">{{ __('Cancelado') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full card bg-base-100 shadow-sm border border-base-300 rounded-2xl p-8 sm:p-12 text-center space-y-4">
                <div class="w-20 h-20 rounded-full bg-error/15 text-error mx-auto flex items-center justify-center text-4xl shadow-sm">
                    ✕
                </div>

                <h3 class="text-2xl sm:text-3xl font-bold text-base-content">
                    {{ __('O processo de pagamento foi cancelado') }}
                </h3>

                <p class="text-sm sm:text-base text-base-content/70 max-w-xl mx-auto leading-relaxed">
                    {{ __('Não foi registado qualquer pagamento nesta tentativa. A sua encomenda não foi confirmada e os livros continuam guardados no seu carrinho de compras.') }}
                </p>

                <div class="pt-2 pb-4">
                    <div class="inline-flex items-center gap-4 bg-base-200/60 px-5 py-3 rounded-xl border border-base-300 text-xs sm:text-sm">
                        <span>{{ __('Referência') }}: <strong class="font-mono text-primary">{{ $encomenda->numero }}</strong></span>
                        <span class="text-base-content/30">|</span>
                        <span>{{ __('Valor Total') }}: <strong class="font-mono text-base-content">{{ $encomenda->total_formatado }}</strong></span>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row justify-center gap-3 pt-2">
                    <a href="{{ route('checkout.pagamento', $encomenda) }}" class="btn btn-primary font-bold shadow-md">
                        {{ __('Tentar Novamente o Pagamento') }}
                    </a>
                    <a href="{{ route('carrinho.index') }}" class="btn btn-outline btn-neutral font-medium">
                        {{ __('Regressar ao Carrinho') }}
                    </a>
                    <a href="{{ url('/') }}" class="btn btn-ghost font-medium">
                        {{ __('Explorar Outros Livros') }}
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>