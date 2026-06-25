<?php

namespace App\Mail;

use App\Enums\RfqStatusEnum;
use App\Models\Rfq;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RfqStatusUpdatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Rfq $rfq, public RfqStatusEnum $newStatus)
    {
        //
    }

    public function envelope(): Envelope
    {
        $subject = match ($this->newStatus) {
            RfqStatusEnum::QUOTED           => "Your RFQ Has Been Quoted — Elbildad Services",
            RfqStatusEnum::AWAITING_PAYMENT => "Payment Required for Your RFQ — Elbildad Services",
            RfqStatusEnum::PAYMENT_RECEIVED => "Payment Confirmed! Your Order is Being Processed",
            RfqStatusEnum::SOURCING         => "We Are Sourcing Your Items — Elbildad Services",
            RfqStatusEnum::PURCHASED        => "Your Items Have Been Purchased — Elbildad Services",
            RfqStatusEnum::SHIPPED          => "Your Order Has Been Shipped — Elbildad Services",
            RfqStatusEnum::COMPLETED        => "Order Completed — Thank You! | Elbildad Services",
            RfqStatusEnum::NOT_FOUND        => "Update on Your RFQ — Elbildad Services",
            default                         => "Your RFQ Status Has Been Updated — Elbildad Services",
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.rfq.status-updated',
            with: [
                'rfq'           => $this->rfq,
                'newStatus'     => $this->newStatus,
                'customerName'  => $this->rfq->customer?->name ?? 'Valued Customer',
                'trackingUrl'   => route('rfq.track', ['token' => $this->rfq->tracking_token]),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
