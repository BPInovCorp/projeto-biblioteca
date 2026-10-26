<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-base-content leading-tight">
            {{ __('Finalizar Encomenda') }}
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

                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary text-primary-content flex items-center justify-center font-bold text-sm shadow">
                            2
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-wider font-bold text-primary">{{ __('Passo 2') }}</div>
                            <div class="text-sm font-bold text-base-content">{{ __('Morada de Entrega') }}</div>
                        </div>
                    </div>

                    <div class="h-0.5 flex-1 bg-base-300 mx-4 sm:mx-6"></div>

                    <div class="flex items-center gap-3 opacity-50">
                        <div class="w-10 h-10 rounded-full bg-base-300 text-base-content flex items-center justify-center font-bold text-sm">
                            3
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-wider font-semibold text-base-content/60">{{ __('Passo 3') }}</div>
                            <div class="text-sm font-medium text-base-content">{{ __('Pagamento') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="w-full alert alert-error shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <div class="font-bold">{{ __('Por favor, corrija os erros assinalados no formulário:') }}</div>
                        <ul class="list-disc list-inside text-xs mt-1">
                            @foreach ($errors->all() as $erro)
                                <li>{{ $erro }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('checkout.guardar-morada') }}" class="space-y-6">
                @csrf

                <div class="w-full card bg-base-100 shadow-sm border border-base-300 rounded-2xl p-6 sm:p-8">
                    <div class="border-b border-base-200 pb-4 mb-6">
                        <h3 class="text-lg font-bold text-base-content">
                            {{ __('Dados do Destinatário e Morada de Entrega') }}
                        </h3>
                        <p class="text-xs text-base-content/70 mt-1">
                            {{ __('Indique os dados onde pretende receber a sua encomenda de livros.') }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-control md:col-span-2">
                            <label class="label">
                                <span class="label-text font-bold text-xs uppercase tracking-wider text-base-content/80">
                                    {{ __('Nome Completo do Destinatário') }} *
                                </span>
                            </label>
                            <input type="text" name="nome_destinatario" value="{{ old('nome_destinatario', $user->name) }}" required class="input input-bordered w-full bg-base-100 @error('nome_destinatario') input-error @enderror" />
                            @error('nome_destinatario')
                                <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-control md:col-span-2">
                            <label class="label">
                                <span class="label-text font-bold text-xs uppercase tracking-wider text-base-content/80">
                                    {{ __('Morada de Entrega (Rua, Número, Andar)') }} *
                                </span>
                            </label>
                            <input type="text" name="morada" value="{{ old('morada') }}" required placeholder="Ex: Av. da Liberdade, n.º 120, 3.º Dto." class="input input-bordered w-full bg-base-100 @error('morada') input-error @enderror" />
                            @error('morada')
                                <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-bold text-xs uppercase tracking-wider text-base-content/80">
                                    {{ __('Código Postal') }} *
                                </span>
                            </label>
                            <input type="text" name="codigo_postal" value="{{ old('codigo_postal') }}" required placeholder="Ex: 1250-140" class="input input-bordered w-full bg-base-100 @error('codigo_postal') input-error @enderror" />
                            @error('codigo_postal')
                                <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-bold text-xs uppercase tracking-wider text-base-content/80">
                                    {{ __('Cidade / Localidade') }} *
                                </span>
                            </label>
                            <input type="text" name="cidade" value="{{ old('cidade') }}" required placeholder="Ex: Lisboa" class="input input-bordered w-full bg-base-100 @error('cidade') input-error @enderror" />
                            @error('cidade')
                                <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-bold text-xs uppercase tracking-wider text-base-content/80">
                                    {{ __('País') }} *
                                </span>
                            </label>
                            <input type="text" name="pais" value="{{ old('pais', 'Portugal') }}" required class="input input-bordered w-full bg-base-100 @error('pais') input-error @enderror" />
                            @error('pais')
                                <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-bold text-xs uppercase tracking-wider text-base-content/80">
                                    {{ __('Telefone / Contacto Móvel') }} ({{ __('Opcional') }})
                                </span>
                            </label>
                            <input type="text" name="telefone" value="{{ old('telefone') }}" placeholder="Ex: 912345678" class="input input-bordered w-full bg-base-100 @error('telefone') input-error @enderror" />
                            @error('telefone')
                                <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="w-full card bg-base-100 shadow-sm border border-base-300 rounded-2xl p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                        <div class="space-y-3">
                            <h3 class="text-lg font-bold text-base-content border-b border-base-200 pb-2">
                                {{ __('Resumo da Encomenda') }}
                            </h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between text-base-content/80">
                                    <span>{{ __('Artigos a Enviar') }} ({{ $totalItens }})</span>
                                    <span class="font-mono font-medium">{{ number_format($totalCentimos / 100, 2, ',', '.') }} €</span>
                                </div>
                                <div class="flex justify-between items-center text-base-content/80">
                                    <span>{{ __('Portes de Envio') }}</span>
                                    <span class="badge badge-success badge-sm text-white font-bold">{{ __('Gratuito') }}</span>
                                </div>
                            </div>
                            <div class="pt-2 flex justify-between items-baseline border-t border-base-200">
                                <span class="text-base font-bold text-base-content">{{ __('Total a Pagar') }}</span>
                                <span class="text-2xl font-black text-primary font-mono">
                                    {{ number_format($totalCentimos / 100, 2, ',', '.') }} €
                                </span>
                            </div>
                        </div>

                        <div class="flex flex-col gap-3 justify-center">
                            <button type="submit" class="btn btn-primary btn-block shadow-md font-bold text-sm">
                                {{ __('Avançar para Pagamento') }}
                            </button>

                            <div class="flex items-center justify-between pt-1">
                                <a href="{{ route('carrinho.index') }}" class="btn btn-ghost btn-sm text-xs inline-flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                    {{ __('Voltar ao Carrinho') }}
                                </a>
                                <span class="text-[11px] text-base-content/50">
                                    {{ __('Ambiente seguro Stripe Sandbox') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>