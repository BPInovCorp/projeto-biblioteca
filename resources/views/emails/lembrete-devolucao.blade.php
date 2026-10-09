<x-mail::message>
# {{ __('Lembrete de Devolução de Livro') }}

{{ __('Olá, :nome!', ['nome' => $requisicao->user->name]) }}

{{ __('Recordamos que o prazo previsto de devolução da obra ":livro" termina amanhã (:data).', ['livro' => $requisicao->livro->nome, 'data' => $requisicao->data_fim_prevista->format('d/m/Y')]) }}

<x-mail::panel>
**{{ __('Nº de Registo:') }}** #{{ str_pad($requisicao->numero_sequencial, 4, '0', STR_PAD_LEFT) }}  
**{{ __('Obra:') }}** {{ $requisicao->livro->nome }}  
**{{ __('Autor(es):') }}** {{ $requisicao->livro->autores->pluck('nome')->implode(', ') }}  
**{{ __('Data da Requisição:') }}** {{ $requisicao->data_requisicao->format('d/m/Y') }}  
**{{ __('Data Limite de Devolução:') }}** {{ $requisicao->data_fim_prevista->format('d/m/Y') }}  
</x-mail::panel>

@php
    $capaRaw = $requisicao->livro->imagem_capa;
    $capaSrc = null;

    if (!empty($capaRaw)) {
        if (\Illuminate\Support\Str::startsWith($capaRaw, ['http://', 'https://'])) {
            $capaSrc = $capaRaw;
        } elseif (file_exists(public_path($capaRaw)) && !is_dir(public_path($capaRaw))) {
            $capaSrc = $message->embed(public_path($capaRaw));
        } else {
            $capaSrc = asset($capaRaw);
        }
    }
@endphp

@if($capaSrc)
<p align="center">
<img src="{{ $capaSrc }}" alt="{{ $requisicao->livro->nome }}" width="140">
</p>
@endif

{{ __('Por favor, dirija-se à biblioteca para proceder à entrega do livro no prazo acordado.') }}

<x-mail::button :url="route('requisicoes.index')">
{{ __('Ver Requisições') }}
</x-mail::button>

{{ __('Obrigado pela sua colaboração!') }}<br>
{{ config('app.name', 'Biblioteca') }}
</x-mail::message>