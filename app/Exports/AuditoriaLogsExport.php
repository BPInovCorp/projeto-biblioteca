<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AuditoriaLogsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        protected Builder $query
    ) {}

    public function query(): Builder
    {
        return $this->query->latest('created_at')->latest('id');
    }

    public function headings(): array
    {
        return [
            'Data',
            'Hora',
            'Utilizador',
            'Módulo',
            'ID Objeto',
            'Alteração',
            'Endereço IP',
            'Navegador',
        ];
    }

    public function map($row): array
    {
        $utilizador = $row->user
            ? "{$row->user->name} ({$row->user->email})"
            : 'Sistema / Anónimo';

        return [
            $row->created_at->format('d/m/Y'),
            $row->created_at->format('H:i:s'),
            $utilizador,
            $row->modulo,
            $row->objeto_id ? '#' . $row->objeto_id : '—',
            $row->alteracao,
            $row->ip ?? '—',
            $row->browser ?? 'Desconhecido',
        ];
    }
}