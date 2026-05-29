<?php

namespace App\Mail;

use App\Models\Rfq;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RfqSummaryMail extends Mailable
{
    use Queueable, SerializesModels;

    public Rfq $rfq;

    public function __construct(Rfq $rfq)
    {
        $this->rfq = $rfq;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your RFQ #{$this->rfq->id} — We're On It!",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.rfq.summary',
            with: [
                'customerName' => $this->rfq->customer?->name ?? 'Valued Customer',
                'productName' => $this->rfq->product_name,
                'aiSummary' => $this->rfq->ai_summary,
                'suppliers' => $this->rfq->ai_suppliers ?? [],
                'trackingUrl' => route('rfq.track', ['token' => $this->rfq->tracking_token]),
            ],
        );
    }
}
