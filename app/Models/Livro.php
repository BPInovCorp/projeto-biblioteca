<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Livro extends Model
{
    use HasFactory;

    protected $table = 'livros';

    protected $fillable = [
        'editora_id',
        'isbn',
        'nome',
        'bibliografia',
        'imagem_capa',
        'preco',
    ];

    protected $casts = [
        'isbn' => 'encrypted',
        'nome' => 'encrypted',
        'bibliografia' => 'encrypted',
        'preco' => 'decimal:2',
    ];

    public function editora(): BelongsTo
    {
        return $this->belongsTo(Editora::class, 'editora_id');
    }

    public function autores(): BelongsToMany
    {
        return $this->belongsToMany(Autor::class, 'autor_livro', 'livro_id', 'autor_id')->withTimestamps();
    }
}