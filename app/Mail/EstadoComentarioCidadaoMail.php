<?php

namespace App\Mail;

use App\Models\Comentario;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EstadoComentarioCidadaoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Comentario $comentario
    ) {}

    public function envelope(): Envelope
    {
        $tituloLivro = $this->comentario->livro->nome ?? 'Obra';

        $assunto = $this->comentario->status === 'ativo'
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