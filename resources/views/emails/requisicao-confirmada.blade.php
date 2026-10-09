<x-mail::message>
# @if($paraAdmin)
{{ __('Nova Requisição Registada') }}
@else
{{ __('Confirmação de Requisição') }}
@endif

@if($paraAdmin)
{{ __('Informamos que foi registada uma nova requisição na biblioteca pelo cidadão :nome.', ['nome' => $requisicao->user->name]) }}
@else
{{ __('Olá, :nome! A sua requisição foi registada com sucesso na biblioteca.', ['nome' => $requisicao->user->name]) }}
@endif

<x-mail::panel>
**{{ __('Nº de Registo:') }}** #{{ str_pad($requisicao->numero_sequencial, 4, '0', STR_PAD_LEFT) }}  
**{{ __('Obra:') }}** {{ $requisicao->livro->nome }}  
**{{ __('Autor(es):') }}** {{ $requisicao->livro->autores->pluck('nome')->implode(', ') }}  
**{{ __('Editora:') }}** {{ optional($requisicao->livro->editora)->nome ?? 'N/D' }}  
**{{ __('Cidadão Requisitante:') }}** {{ $requisicao->user->name }} ({{ $requisicao->user->email }})  
**{{ __('Data da Requisição:') }}** {{ $requisicao->data_requisicao->format('d/m/Y') }}  
**{{ __('Data Prevista de Devolução (+5 dias):') }}** {{ $requisicao->data_fim_prevista->format('d/m/Y') }}  
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

<x-mail::button :url="route('requisicoes.index')">
{{ __('Ver Requisições') }}
</x-mail::button>

{{ __('Obrigado por utilizar os serviços da nossa Biblioteca!') }}<br>
{{ config('app.name', 'Biblioteca') }}
</x-mail::message>