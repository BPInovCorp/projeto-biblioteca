<?php

namespace App\Http\Controllers;

use App\Models\Alerta;
use App\Models\Livro;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class AlertaController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'livro_id' => ['required', 'integer', 'exists:livros,id'],
        ]);

        $user = $request->user();
        $livro = Livro::findOrFail($validated['livro_id']);

        if ($livro->isDisponivel()) {
            return redirect()->back()->with('error', __('Este livro já se encontra disponível para requisição imediata.'));
        }

        $alertaExistente = Alerta::where('user_id', $user->id)
            ->where('livro_id', $livro->id)
            ->first();

        if ($alertaExistente) {
            if ($alertaExistente->notificado_at === null) {
                return redirect()->back()->with('error', __('Já tem um alerta ativo para este livro.'));
            }

            $alertaExistente->update([
                'notificado_at' => null,
            ]);

            return redirect()->back()->with('status', __('Subscreveu com sucesso o alerta de disponibilidade para esta obra.'));
        }

        try {
            Alerta::create([
                'user_id' => $user->id,
                'livro_id' => $livro->id,
                'notificado_at' => null,
            ]);
        } catch (Throwable) {
            return redirect()->back()->with('error', __('Já tem um alerta ativo para este livro.'));
        }

        return redirect()->back()->with('status', __('Subscreveu com sucesso o alerta de disponibilidade para esta obra.'));
    }
}