<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alerta extends Model
{
    protected $table = 'alertas';

    protected $fillable = [
        'user_id',
        'livro_id',
        'notificado_at',
    ];

    protected function casts(): array
    {
        return [
            'notificado_at' => 'datetime',
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

    public function scopePendentes(Builder $query): Builder
    {
        return $query->whereNull('notificado_at');
    }
}