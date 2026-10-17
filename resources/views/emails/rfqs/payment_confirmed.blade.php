@component('mail::message')
# Commitment Fee Received!

Hi **{{ $rfq->customer->name ?? 'Valued Customer' }}**,

We have successfully received your commitment fee payment of **₦{{ number_format($transaction->amount, 2) }}** for your RFQ application.

---

**Application Details**

| | |
|---|---|
| **Product** | {{ $rfq->product_name }} |
| **Tracking Token** | `{{ $rfq->tracking_token }}` |
| **Payment Reference** | `{{ $transaction->reference }}` |
| **Fee Paid** | ₦{{ number_format($transaction->amount, 2) }} |

---

Your request is now in our processing pipeline. Our team and AI sourcing system are working to find the best suppliers for your product. You will receive another update once we have results.

@component('mail::button', ['url' => url('/tracking/' . $rfq->tracking_token), 'color' => 'green'])
Track Your RFQ
@endcomponent

**Why a commitment fee?** This fee verifies your request and ensures our dedicated agents can prioritize and commit their full resources to sourcing the perfect item for you.

If you have any questions, feel free to reply to this email or contact us via WhatsApp.

Thanks,
**Elbildad Services Ltd**
@endcomponent
