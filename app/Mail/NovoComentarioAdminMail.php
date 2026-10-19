<?php

namespace App\Mail;

use App\Models\Comentario;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NovoComentarioAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Comentario $comentario
    ) {}

    public function envelope(): Envelope
    {
        $tituloLivro = $this->comentario->livro->nome ?? 'Obra';

        return new Envelope(
            subject: '[Biblioteca] Nova Avaliação Pendente de Moderação - Livro: ' . $tituloLivro,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.novo-comentario-admin',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}