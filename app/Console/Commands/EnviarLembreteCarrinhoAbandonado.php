<?php

namespace App\Console\Commands;

use App\Mail\CarrinhoAbandonadoMail;
use App\Models\Encomenda;
use App\Models\User;
use App\Services\CarrinhoService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class EnviarLembreteCarrinhoAbandonado extends Command
{
    protected $signature = 'biblioteca:lembrete-carrinho-abandonado';

    protected $description = 'Envia notificações por e-mail a cidadãos com artigos no carrinho inativos há mais de 1 hora.';

    public function handle(CarrinhoService $carrinhoService): int
    {
        $limiteInatividade = now()->subHour();

        $utilizadores = User::whereHas('carrinhoItems', function ($q) use ($limiteInatividade) {
            $q->where('updated_at', '<=', $limiteInatividade);
        })->get();

        $totalEnviados = 0;

        foreach ($utilizadores as $user) {
            try {
                $enviado = DB::transaction(function () use ($user, $limiteInatividade, $carrinhoService) {
                    $itens = $user->carrinhoItems()
                        ->with(['livro.autores', 'livro.editora'])
                        ->lockForUpdate()
                        ->get();

                    if ($itens->isEmpty()) {
                        return false;
                    }

                    $ultimaAtividade = $itens->max('updated_at');
                    if ($ultimaAtividade > $limiteInatividade) {
                        return false;
                    }

                    $precisaAviso = $itens->contains(function ($item) {
                        return is_null($item->aviso_abandono_enviado_em)
                            || $item->aviso_abandono_enviado_em < $item->updated_at;
                    });

                    if (! $precisaAviso) {
                        return false;
                    }

                    $temEncomendaRecente = $user->encomendas()
                        ->where('estado_pagamento', Encomenda::PAGAMENTO_PAGO)
                        ->where('updated_at', '>=', $limiteInatividade)
                        ->exists();

                    if ($temEncomendaRecente) {
                        return false;
                    }

                    $user->carrinhoItems()
                        ->whereIn('id', $itens->pluck('id'))
                        ->update([
                            'aviso_abandono_enviado_em' => now(),
                        ]);

                    $totalCentimos = $carrinhoService->calcularTotalCentimos($user);

                    Mail::to($user->email)->queue(
                        new CarrinhoAbandonadoMail($user, $itens, $totalCentimos)
                    );

                    return true;
                });

                if ($enviado) {
                    $totalEnviados++;
                    $this->info("Lembrete agendado para o cidadão: {$user->email}");
                }
            } catch (Exception $e) {
                $this->error("Erro ao processar lembrete para {$user->email}: {$e->getMessage()}");
            }
        }

        $this->info("Processamento concluído. Total de lembretes colocados na fila: {$totalEnviados}");

        return Command::SUCCESS;
    }
}