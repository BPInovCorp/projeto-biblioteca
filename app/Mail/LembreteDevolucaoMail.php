<?php

namespace App\Mail;

use App\Models\Requisicao;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LembreteDevolucaoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Requisicao $requisicao
    ) {}

    public function envelope(): Envelope
    {
        $numero = '#' . str_pad($this->requisicao->numero_sequencial, 4, '0', STR_PAD_LEFT);

        return new Envelope(
            subject: '[Biblioteca] Lembrete: Devolução Prevista para Amanhã - ' . $numero,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.lembrete-devolucao',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}