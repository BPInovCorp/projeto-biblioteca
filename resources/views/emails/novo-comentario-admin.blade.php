<x-mail::message>
# {{ __('Nova Avaliação Pendente de Moderação') }}

{{ __('Informamos que o cidadão :nome submeteu uma nova avaliação crítica para a obra ":livro".', ['nome' => $comentario->user->name, 'livro' => $comentario->livro->nome]) }}

<x-mail::panel>
**{{ __('Cidadão:') }}** {{ $comentario->user->name }} ({{ $comentario->user->email }})  
**{{ __('Obra:') }}** {{ $comentario->livro->nome }}  
**{{ __('Classificação:') }}** {{ str_repeat('⭐', $comentario->classificacao) }} ({{ $comentario->classificacao }} / 5)  
**{{ __('Data de Submissão:') }}** {{ $comentario->created_at->format('d/m/Y H:i') }}  
**{{ __('Estado Inicial:') }}** {{ __('Suspenso / A aguardar validação') }}  
</x-mail::panel>

### {{ __('Texto da Crítica Literária:') }}
> {{ $comentario->comentario }}

<x-mail::button :url="route('admin.comentarios.index', ['estado' => 'suspenso'])">
{{ __('Aceder à Moderação de Avaliações') }}
</x-mail::button>

{{ __('Por favor, aceda ao painel de administração para aprovar ou recusar esta submissão.') }}<br>
{{ config('app.name', 'Biblioteca') }}
</x-mail::message>