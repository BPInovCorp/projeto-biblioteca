<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Autor extends Model
{
    use HasFactory;

    protected $table = 'autores';

    protected $fillable = [
        'nome',
        'foto',
    ];

    protected $casts = [
        'nome' => 'encrypted',
    ];

    public function livros(): BelongsToMany
    {
        return $this->belongsToMany(Livro::class, 'autor_livro', 'autor_id', 'livro_id')->withTimestamps();
    }
}