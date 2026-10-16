<?php

namespace App\Http\Controllers;

use App\Models\Comentario;
use App\Models\Requisicao;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComentarioController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'requisicao_id' => ['required', 'integer', 'exists:requisicoes,id'],
            'classificacao' => ['required', 'integer', 'min:1', 'max:5'],
            'comentario' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $user = $request->user();
        $requisicao = Requisicao::findOrFail($validated['requisicao_id']);

        if ($requisicao->user_id !== $user->id) {
            return redirect()->back()->with('error', __('Não tem autorização para avaliar esta requisição.'));
        }

        if ($requisicao->status !== 'entregue') {
            return redirect()->back()->with('error', __('Apenas é possível avaliar livros após a confirmação da devolução.'));
        }

        if (Comentario::where('requisicao_id', $requisicao->id)->exists()) {
            return redirect()->back()->with('error', __('Já submeteu uma avaliação para esta requisição.'));
        }

        DB::transaction(function () use ($validated, $user, $requisicao) {
            Comentario::create([
                'user_id' => $user->id,
                'livro_id' => $requisicao->livro_id,
                'requisicao_id' => $requisicao->id,
                'classificacao' => $validated['classificacao'],
                'comentario' => trim($validated['comentario']),
                'status' => 'suspenso',
                'justificacao' => null,
            ]);
        });

        return redirect()->back()->with('status', __('A sua avaliação foi submetida com sucesso e aguarda moderação.'));
    }
}