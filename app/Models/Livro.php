<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'origem',
        'isbn_hash',
    ];

    protected $casts = [
        'isbn' => 'encrypted',
        'nome' => 'encrypted',
        'bibliografia' => 'encrypted',
        'imagem_capa' => 'encrypted',
        'preco' => 'encrypted',
    ];

    public function editora(): BelongsTo
    {
        return $this->belongsTo(Editora::class, 'editora_id');
    }

    public function autores(): BelongsToMany
    {
        return $this->belongsToMany(Autor::class, 'autor_livro', 'livro_id', 'autor_id')->withTimestamps();
    }

    public function requisicoes(): HasMany
    {
        return $this->hasMany(Requisicao::class);
    }

    public function isDisponivel(): bool
    {
        if ($this->relationLoaded('requisicoes')) {
            return !$this->requisicoes->contains('status', 'ativa');
        }

        return !$this->requisicoes()->where('status', 'ativa')->exists();
    }

    protected static function booted(): void
    {
        static::saving(function (Livro $livro) {
            $livro->isbn_hash = static::hashIsbn($livro->isbn);
        });
    }

    public static function hashIsbn(?string $isbn): ?string
    {
        if (empty($isbn)) {
            return null;
        }

        $limpo = preg_replace('/[^0-9xX]/', '', (string) $isbn);

        if (empty($limpo)) {
            return null;
        }

        return hash_hmac('sha256', $limpo, (string) config('app.key'));
    }
}