<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Editora extends Model
{
    use HasFactory;

    protected $table = 'editoras';

    protected $fillable = [
        'nome',
        'logotipo',
    ];

    protected $casts = [
        'nome' => 'encrypted',
        'logotipo' => 'encrypted',
    ];

    public function livros(): HasMany
    {
        return $this->hasMany(Livro::class, 'editora_id');
    }
}