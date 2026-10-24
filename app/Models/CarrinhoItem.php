<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarrinhoItem extends Model
{
    use HasFactory;

    protected $table = 'carrinho_items';

    protected $fillable = [
        'user_id',
        'livro_id',
        'quantidade',
        'aviso_abandono_enviado_em',
    ];

    protected function casts(): array
    {
        return [
            'quantidade' => 'integer',
            'aviso_abandono_enviado_em' => 'datetime',
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

    public function getSubtotalCentimosAttribute(): int
    {
        return (int) (($this->livro->preco ?? 0) * 100) * $this->quantidade;
    }
}