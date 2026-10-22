<?php

namespace App\Mail;

use App\Models\Comentario;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EstadoComentarioCidadaoMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $estado;

    public ?string $justificacao;

    // Guarda o estado no momento da decisão, para o email em fila não refletir decisões posteriores
    public function __construct(
        public Comentario $comentario
    ) {
        $this->estado = $comentario->status;
        $this->justificacao = $comentario->justificacao;
    }

    public function envelope(): Envelope
    {
        $tituloLivro = $this->comentario->livro->nome ?? 'Obra';

        $assunto = $this->estado === 'ativo'
            ? '[Biblioteca] A sua Avaliação foi Aprovada - ' . $tituloLivro
            : '[Biblioteca] Atualização sobre a sua Avaliação - ' . $tituloLivro;

        return new Envelope(
            subject: $assunto,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.estado-comentario-cidadao',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}