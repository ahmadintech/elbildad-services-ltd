<x-mail::message>
# We have received your RFQ!

Hello {{ $rfq->customer->name ?? 'Customer' }},

Thank you for submitting a Request for Quotation (RFQ) for **{{ $rfq->product_name }}** with Elbildad Services. 

We have successfully received your request, and our team of expert sourcing agents will begin reviewing it immediately to find the best possible suppliers for your needs.

You can securely track the progress of your request at any time using your unique Tracking Token below:

<x-mail::panel>
**Tracking Token:** {{ $rfq->tracking_token }}
</x-mail::panel>

<x-mail::button :url="route('rfq.track', $rfq->tracking_token)" color="primary">
Track Your RFQ
</x-mail::button>

If you have an account with us, you can also log in to your dashboard to manage all your active requests.

If you have any questions or need to make adjustments to your request, please feel free to reply to this email or contact our support team.

Warm regards,<br>
**The {{ config('app.name') }} Team**

<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.<br>
Your trusted and reliable sourcing agency.<br>
<a href="mailto:support@elbildadservices.ng">support@elbildadservices.ng</a>
</x-mail::footer>
</x-slot:footer>
</x-mail::message>
