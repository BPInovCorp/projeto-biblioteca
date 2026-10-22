<x-mail::message>
@if($estado === 'ativo')
# {{ __('Avaliação Aprovada e Publicada') }}

{{ __('Olá, :nome!', ['nome' => $comentario->user->name]) }}

{{ __('Temos o prazer de informar que a sua avaliação crítica da obra ":livro" foi aprovada pela equipa de moderação e já se encontra visível publicamente no catálogo da biblioteca.', ['livro' => $comentario->livro->nome]) }}

<x-mail::panel>
**{{ __('Obra:') }}** {{ $comentario->livro->nome }}  
**{{ __('Classificação:') }}** {{ str_repeat('⭐', $comentario->classificacao) }} ({{ $comentario->classificacao }} / 5)  
**{{ __('Estado:') }}** {{ __('Aprovada / Publicada') }}  
</x-mail::panel>

{{ __('O seu comentário:') }}  
> {{ $comentario->comentario }}

@else
# {{ __('Atualização sobre a sua Avaliação') }}

{{ __('Olá, :nome!', ['nome' => $comentario->user->name]) }}

{{ __('Agradecemos a sua submissão. No entanto, após análise da nossa equipa de moderação, a sua avaliação da obra ":livro" não pôde ser aprovada para publicação.', ['livro' => $comentario->livro->nome]) }}

<x-mail::panel>
**{{ __('Obra:') }}** {{ $comentario->livro->nome }}  
**{{ __('Estado:') }}** {{ __('Recusada') }}  
**{{ __('Justificação da Moderação:') }}**  
{{ $justificacao }}
</x-mail::panel>

{{ __('O comentário submetido:') }}  
> {{ $comentario->comentario }}

@endif

<x-mail::button :url="route('requisicoes.index')">
{{ __('Consultar as Minhas Requisições') }}
</x-mail::button>

{{ __('Obrigado pelo seu contributo para a comunidade da nossa Biblioteca!') }}<br>
{{ config('app.name', 'Biblioteca') }}
</x-mail::message>