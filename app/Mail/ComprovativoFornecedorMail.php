<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\FaturaFornecedor;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ComprovativoFornecedorMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public FaturaFornecedor $fatura,
        public string $caminhoComprovativo,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Comprovativo de Pagamento - Fatura {$this->fatura->numero}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.comprovativo_fornecedor',
            with: [
                'fatura' => $this->fatura,
                'fornecedor' => $this->fatura->entidade,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromStorageDisk('private', $this->caminhoComprovativo)
                ->as('Comprovativo-'.$this->fatura->numero.'.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
