<?php

namespace App\Http\Controllers;

use App\Exports\AuditoriaLogsExport;
use App\Models\AuditoriaLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdminLogController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'modulo' => ['nullable', 'string'],
            'user_id' => ['nullable'],
            'search' => ['nullable', 'string', 'max:255'],
            'data_inicio' => ['nullable', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
        ]);

        $moduloSelecionado = $request->input('modulo');
        $userSelecionado = $request->input('user_id');
        $pesquisa = $request->input('search');
        $dataInicio = $request->input('data_inicio');
        $dataFim = $request->input('data_fim');

        $query = $this->obterQueryFiltrada($request);

        $logs = $query->latest('created_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $modulos = AuditoriaLog::distinct()
            ->pluck('modulo')
            ->filter()
            ->sort()
            ->values();

        $utilizadores = User::whereHas('auditoriaLogs')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $totalLogs = AuditoriaLog::count();

        return view('admin.logs.index', compact(
            'logs',
            'modulos',
            'utilizadores',
            'moduloSelecionado',
            'userSelecionado',
            'pesquisa',
            'dataInicio',
            'dataFim',
            'totalLogs'
        ));
    }

    public function obterQueryFiltrada(Request $request): Builder
    {
        $query = AuditoriaLog::with('user');

        $modulo = $request->input('modulo');
        if (! empty($modulo) && $modulo !== 'todos') {
            $query->where('modulo', $modulo);
        }

        $userId = $request->input('user_id');
        if (! empty($userId) && $userId !== 'todos') {
            $query->where('user_id', $userId);
        }

        $pesquisa = $request->input('search');
        if (! empty($pesquisa)) {
            $query->where(function ($q) use ($pesquisa) {
                $q->where('alteracao', 'like', "%{$pesquisa}%")
                    ->orWhere('objeto_id', 'like', "%{$pesquisa}%")
                    ->orWhere('ip', 'like', "%{$pesquisa}%")
                    ->orWhereHas('user', function ($userQuery) use ($pesquisa) {
                        $userQuery->where('name', 'like', "%{$pesquisa}%")
                            ->orWhere('email', 'like', "%{$pesquisa}%");
                    });
            });
        }

        $dataInicio = $request->input('data_inicio');
        if (! empty($dataInicio)) {
            $query->where('created_at', '>=', Carbon::parse($dataInicio)->startOfDay());
        }

        $dataFim = $request->input('data_fim');
        if (! empty($dataFim)) {
            $query->where('created_at', '<=', Carbon::parse($dataFim)->endOfDay());
        }

        return $query;
    }

    public function export(request $request): binaryfileresponse
    {
        $request->validate([
            'modulo' => ['nullable', 'string'],
            'user_id' => ['nullable'],
            'search' => ['nullable', 'string', 'max:255'],
            'data_inicio' => ['nullable', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
            'formato' => ['nullable', 'in:xlsx,csv'],
        ]);

        $formato = $request->input('formato', 'xlsx');
        $extensao = $formato === 'csv' ? 'csv' : 'xlsx';
        $nomeficheiro = 'auditoria_logs_' . now()->format('y-m-d_his') . '.' . $extensao;

        $query = $this->obterqueryfiltrada($request);

        return excel::download(new auditorialogsexport($query), $nomeficheiro);
    }
}