<?php

namespace App\Exports;

use App\Models\Livro;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LivrosExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection(): Collection
    {
        return Livro::with(['editora', 'autores'])->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'ISBN',
            'Nome',
            'Editora',
            'Autores',
            'Preço',
            'Origem',
            'Bibliografia / Sinopse',
            'Imagem da Capa',
            'Data de Registo',
        ];
    }

    public function map(mixed $livro): array
    {
        $origem = (Str::startsWith((string) $livro->imagem_capa, ['http://', 'https://']) || Str::startsWith((string) $livro->isbn, 'SEM-ISBN-'))
            ? 'Google Books'
            : 'Acervo Local';

        $precoFormatado = is_numeric($livro->preco)
            ? number_format((float) $livro->preco, 2, ',', '.') . ' €'
            : ($livro->preco ?? 'Preço sob consulta');

        return [
            $livro->id,
            $livro->isbn,
            $livro->nome,
            $livro->editora->nome ?? '',
            $livro->autores->pluck('nome')->implode(', '),
            $precoFormatado,
            $origem,
            $livro->bibliografia,
            $livro->imagem_capa,
            $livro->created_at?->format('d/m/Y H:i'),
        ];
    }
}