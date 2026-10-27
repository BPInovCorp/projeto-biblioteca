<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CarrinhoAbandonadoMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public Collection $itens,
        public int $totalCentimos
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Precisa de ajuda com os livros no seu carrinho? - Biblioteca',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.carrinho-abandonado',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}