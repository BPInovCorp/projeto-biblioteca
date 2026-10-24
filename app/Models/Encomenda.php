<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Encomenda extends Model
{
    use HasFactory;

    public const ESTADO_PENDENTE = 'pendente';
    public const ESTADO_PROCESSAMENTO = 'processamento';
    public const ESTADO_ENVIADA = 'enviada';
    public const ESTADO_CONCLUIDA = 'concluida';
    public const ESTADO_CANCELADA = 'cancelada';

    public const PAGAMENTO_PENDENTE = 'pendente';
    public const PAGAMENTO_PAGO = 'pago';
    public const PAGAMENTO_FALHADO = 'falhado';
    public const PAGAMENTO_REEMBOLSADO = 'reembolsado';

    protected $table = 'encomendas';

    protected $fillable = [
        'user_id',
        'numero',
        'total_centimos',
        'estado',
        'estado_pagamento',
        'nome_destinatario',
        'morada',
        'codigo_postal',
        'cidade',
        'pais',
        'telefone',
        'stripe_session_id',
        'stripe_payment_intent_id',
    ];

    protected function casts(): array
    {
        return [
            'total_centimos' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(EncomendaItem::class);
    }

    public function scopePagas(Builder $query): Builder
    {
        return $query->where('estado_pagamento', self::PAGAMENTO_PAGO);
    }

    public function scopePendentes(Builder $query): Builder
    {
        return $query->where('estado_pagamento', self::PAGAMENTO_PENDENTE);
    }

    public function getTotalFormatadoAttribute(): string
    {
        return number_format($this->total_centimos / 100, 2, ',', '.') . ' €';
    }
}