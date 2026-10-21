<x-mail::message>
# {{ __('Livro Disponível para Requisição') }}

{{ __('Olá, :nome!', ['nome' => $user->name]) }}

{{ __('Temos o prazer de informar que o livro ":livro" que pretendia consultar já se encontra novamente disponível no acervo da nossa Biblioteca.', ['livro' => $livro->nome]) }}

<x-mail::panel>
**{{ __('Obra:') }}** {{ $livro->nome }}  
**{{ __('Autor(es):') }}** {{ $livro->autores->pluck('nome')->implode(', ') }}  
**{{ __('Editora:') }}** {{ optional($livro->editora)->nome ?? 'N/D' }}  
**{{ __('Estado:') }}** {{ __('Disponível para Requisição Imediata') }}  
</x-mail::panel>

@php
    $capaRaw = $livro->imagem_capa;
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
<img src="{{ $capaSrc }}" alt="{{ $livro->nome }}" width="140">
</p>
@endif

{{ __('Nota: Este alerta tem caráter exclusivamente informativo e não constitui uma reserva formal do exemplar. Recomendamos que aceda à plataforma para formalizar a sua requisição.') }}

<x-mail::button :url="url('/')">
{{ __('Requisitar Livro na Estante') }}
</x-mail::button>

{{ __('Boas leituras!') }}<br>
{{ config('app.name', 'Biblioteca') }}
</x-mail::message>