<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EncomendaItem extends Model
{
    use HasFactory;

    protected $table = 'encomenda_items';

    protected $fillable = [
        'encomenda_id',
        'livro_id',
        'quantidade',
        'preco_unitario_centimos',
    ];

    protected function casts(): array
    {
        return [
            'quantidade' => 'integer',
            'preco_unitario_centimos' => 'integer',
        ];
    }

    public function encomenda(): BelongsTo
    {
        return $this->belongsTo(Encomenda::class);
    }

    public function livro(): BelongsTo
    {
        return $this->belongsTo(Livro::class);
    }

    public function getSubtotalCentimosAttribute(): int
    {
        return $this->preco_unitario_centimos * $this->quantidade;
    }

    public function getSubtotalFormatadoAttribute(): string
    {
        return number_format($this->subtotal_centimos / 100, 2, ',', '.') . ' €';
    }

    public function getPrecoUnitarioFormatadoAttribute(): string
    {
        return number_format($this->preco_unitario_centimos / 100, 2, ',', '.') . ' €';
    }
}