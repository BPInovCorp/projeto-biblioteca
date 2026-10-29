<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditoriaLog extends Model
{
    use HasFactory;

    protected $table = 'auditoria_logs';

    protected $fillable = [
        'user_id',
        'modulo',
        'objeto_id',
        'alteracao',
        'ip',
        'browser',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePorModulo(Builder $query, ?string $modulo): Builder
    {
        if (empty($modulo) || $modulo === 'todos') {
            return $query;
        }

        return $query->where('modulo', $modulo);
    }

    public function scopePorUtilizador(Builder $query, ?int $userId): Builder
    {
        if (empty($userId)) {
            return $query;
        }

        return $query->where('user_id', $userId);
    }

    public function getDataFormatadaAttribute(): string
    {
        return $this->created_at->format('d/m/Y');
    }

    public function getHoraFormatadaAttribute(): string
    {
        return $this->created_at->format('H:i:s');
    }

    public function getDataHoraFormatadaAttribute(): string
    {
        return $this->created_at->format('d/m/Y H:i:s');
    }
}