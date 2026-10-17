<?php

namespace App\Mail;

use App\Models\Rfq;
use App\Models\Transaction;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Sent immediately (not queued) when payment is confirmed via Zainpay webhook
 * or manual verification. Does NOT implement ShouldQueue.
 */
class PaymentConfirmedEmail extends Mailable
{
    public function __construct(
        public Rfq $rfq,
        public Transaction $transaction
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Commitment Fee Received – Your RFQ is now being processed',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.rfqs.payment_confirmed',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
