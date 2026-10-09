<?php

namespace App\Mail;

use App\Models\Requisicao;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RequisicaoConfirmadaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Requisicao $requisicao,
        public bool $paraAdmin = false
    ) {}

    public function envelope(): Envelope
    {
        $numero = '#' . str_pad($this->requisicao->numero_sequencial, 4, '0', STR_PAD_LEFT);

        $assunto = $this->paraAdmin
            ? '[Biblioteca] Nova Requisição Registada - ' . $numero
            : '[Biblioteca] Confirmação de Requisição - ' . $numero;

        return new Envelope(
            subject: $assunto,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.requisicao-confirmada',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}