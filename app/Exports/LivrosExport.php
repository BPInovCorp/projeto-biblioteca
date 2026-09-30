<?php

namespace App\Exports;

use App\Models\Livro;
use Illuminate\Support\Collection;
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
            'Preço (€)',
            'Bibliografia / Sinopse',
            'Imagem da Capa',
            'Data de Registo'
        ];
    }

    public function map(mixed $livro): array
    {
        return [
            $livro->id,
            $livro->isbn,
            $livro->nome,
            $livro->editora->nome ?? '',
            $livro->autores->pluck('nome')->implode(', '),
            $livro->preco,
            $livro->bibliografia,
            $livro->imagem_capa,
            $livro->created_at,
        ];
    }
}