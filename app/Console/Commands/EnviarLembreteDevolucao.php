<?php

namespace App\Console\Commands;

use App\Mail\LembreteDevolucaoMail;
use App\Models\Requisicao;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EnviarLembreteDevolucao extends Command
{
    protected $signature = 'requisicoes:enviar-lembretes';

    protected $description = 'Envia um email de lembrete aos cidadãos com requisições a terminar no dia seguinte (D-1).';

    public function handle(): int
    {
        $amanha = now()->addDay()->toDateString();

        $requisicoes = Requisicao::with(['user', 'livro'])
            ->where('status', 'ativa')
            ->whereDate('data_fim_prevista', $amanha)
            ->get();

        if ($requisicoes->isEmpty()) {
            $this->info("Nenhuma requisição ativa com entrega prevista para amanhã ({$amanha}).");
            return Command::SUCCESS;
        }

        $enviados = 0;
        $falhas = 0;

        foreach ($requisicoes as $requisicao) {
            try {
                if ($requisicao->user && $requisicao->user->email) {
                    Mail::to($requisicao->user->email)->send(new LembreteDevolucaoMail($requisicao));
                    $enviados++;
                    $this->line("Lembrete enviado com sucesso para {$requisicao->user->email} (Registo #{$requisicao->numero_sequencial}).");
                }
            } catch (Throwable $e) {
                $falhas++;
                $this->error("Falha ao enviar lembrete da requisição #{$requisicao->numero_sequencial}: {$e->getMessage()}");
            }
        }

        $this->info("Processamento concluído: {$enviados} lembretes enviados, {$falhas} falhas.");

        return Command::SUCCESS;
    }
}