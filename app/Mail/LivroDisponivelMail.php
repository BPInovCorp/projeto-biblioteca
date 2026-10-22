<?php

namespace App\Mail;

use App\Models\Livro;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LivroDisponivelMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Livro $livro,
        public User $user
    ) {}

    public function envelope(): Envelope
    {
        $titulo = $this->livro->nome ?? 'Obra';

        return new Envelope(
            subject: '[Biblioteca] O livro "' . $titulo . '" já se encontra disponível!',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.livro-disponivel',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}