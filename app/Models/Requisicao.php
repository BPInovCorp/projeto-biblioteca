<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Requisicao extends Model
{
    protected $table = 'requisicoes';

    protected $fillable = [
        'numero_sequencial',
        'user_id',
        'livro_id',
        'foto_cidadao',
        'data_requisicao',
        'data_fim_prevista',
        'data_devolucao',
        'dias_decorridos',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'data_requisicao' => 'date',
            'data_fim_prevista' => 'date',
            'data_devolucao' => 'date',
            'numero_sequencial' => 'integer',
            'dias_decorridos' => 'integer',
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

    public function comentario(): HasOne
    {
        return $this->hasOne(Comentario::class, 'requisicao_id');
    }

    public function scopeAtivas(Builder $query): Builder
    {
        return $query->where('status', 'ativa');
    }

    public function scopeEntregues(Builder $query): Builder
    {
        return $query->where('status', 'entregue');
    }
}