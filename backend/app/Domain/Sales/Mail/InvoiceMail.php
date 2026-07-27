<?php

namespace App\Domain\Sales\Mail;

use App\Domain\Sales\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Sale $sale) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Invoice {$this->sale->invoice_number}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'invoices.invoice',
            with: ['sale' => $this->sale],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $pdf = Pdf::loadView('invoices.invoice', ['sale' => $this->sale]);

        return [
            Attachment::fromData(fn () => $pdf->output(), "{$this->sale->invoice_number}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
