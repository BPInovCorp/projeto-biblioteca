<?php

namespace App\Http\Controllers;

use App\Mail\EstadoComentarioCidadaoMail;
use App\Models\Comentario;
use App\Services\AuditoriaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class AdminComentarioController extends Controller
{
    protected AuditoriaService $auditoriaService;

    public function __construct(AuditoriaService $auditoriaService)
    {
        $this->auditoriaService = $auditoriaService;
    }

    public function index(Request $request): View
    {
        $estado = $request->query('estado', 'suspenso');

        $query = Comentario::with(['user', 'livro.editora', 'requisicao'])->latest('id');

        if (in_array($estado, ['suspenso', 'ativo', 'recusado'], true)) {
            $query->where('status', $estado);
        }

        $comentarios = $query->paginate(15)->withQueryString();

        $totalTodos = Comentario::count();
        $totalSuspensos = Comentario::where('status', 'suspenso')->count();
        $totalAtivos = Comentario::where('status', 'ativo')->count();
        $totalRecusados = Comentario::where('status', 'recusado')->count();

        return view('admin.comentarios.index', compact(
            'comentarios',
            'estado',
            'totalTodos',
            'totalSuspensos',
            'totalAtivos',
            'totalRecusados'
        ));
    }

    public function aprovar(Comentario $comentario): RedirectResponse
    {
        if ($comentario->status === 'ativo') {
            return redirect()->back()->with('error', __('Esta avaliação já se encontra ativa.'));
        }

        $comentario->update([
            'status' => 'ativo',
            'justificacao' => null,
        ]);

        try {
            $comentario->load(['user', 'livro']);

            $this->auditoriaService->registar(
                'avaliações',
                $comentario->id,
                "aprovou a avaliação #{$comentario->id} do livro '{$comentario->livro->nome}'",
                auth::user()
            );

            if ($comentario->user && $comentario->user->email) {
                Mail::to($comentario->user->email)->send(new EstadoComentarioCidadaoMail($comentario));
            }
        } catch (Throwable $e) {
            Log::error('Falha ao enviar email de aprovação ao cidadão: ' . $e->getMessage());
        }

        return redirect()->back()->with('status', __('Avaliação aprovada com sucesso e publicada no catálogo.'));
    }

    public function recusar(Request $request, Comentario $comentario): RedirectResponse
    {
        if ($comentario->status === 'recusado') {
            return redirect()->back()->with('error', __('Esta avaliação já se encontra recusada.'));
        }

        $validated = $request->validate([
            'justificacao' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $comentario->update([
            'status' => 'recusado',
            'justificacao' => trim($validated['justificacao']),
        ]);

        try {
            $comentario->load(['user', 'livro']);

            $this->auditoriaService->registar(
                'Avaliações',
                $comentario->id,
                "Recusou a avaliação #{$comentario->id} do livro '{$comentario->livro->nome}' com a justificação: '{$comentario->justificacao}'",
                $request->user()
            );

            if ($comentario->user && $comentario->user->email) {
                Mail::to($comentario->user->email)->send(new EstadoComentarioCidadaoMail($comentario));
            }
        } catch (Throwable $e) {
            Log::error('Falha ao enviar email de recusa ao cidadão: ' . $e->getMessage());
        }

        return redirect()->back()->with('status', __('Avaliação recusada com sucesso.'));
    }
}