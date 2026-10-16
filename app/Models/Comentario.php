<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comentario extends Model
{
    protected $table = 'reviews';

    protected $fillable = [
        'user_id',
        'livro_id',
        'requisicao_id',
        'classificacao',
        'comentario',
        'status',
        'justificacao',
    ];

    protected function casts(): array
    {
        return [
            'classificacao' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function livro(): BelongsTo
    {
        return $this->belongsTo(Livro::class);
    }

    public function requisicao(): BelongsTo
    {
        return $this->belongsTo(Requisicao::class);
    }

    public function scopeAtivos(Builder $query): Builder
    {
        return $query->where('status', 'ativo');
    }

    public function scopeSuspensos(Builder $query): Builder
    {
        return $query->where('status', 'suspenso');
    }

    public function scopeRecusados(Builder $query): Builder
    {
        return $query->where('status', 'recusado');
    }
}