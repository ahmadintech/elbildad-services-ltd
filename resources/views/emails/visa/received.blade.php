<x-mail::message>
# 🛂 Visa Application Received

Hello {{ $visaApplication->full_name }},

Thank you for submitting your **Chinese Business Visa** application with **Elbildad Services**. We have successfully received your application and our dedicated visa team will begin reviewing it immediately.

<x-mail::panel>
**Application Summary**

- **Visa Type:** {{ $visaApplication->preferred_visa_type }}
- **Purpose of Visit:** {{ $visaApplication->purpose_of_visit }}
@if($visaApplication->intended_arrival_date)
- **Intended Arrival:** {{ \Carbon\Carbon::parse($visaApplication->intended_arrival_date)->format('M d, Y') }}
@endif
@if($visaApplication->intended_departure_date)
- **Intended Departure:** {{ \Carbon\Carbon::parse($visaApplication->intended_departure_date)->format('M d, Y') }}
@endif
- **Passport Number:** {{ Str::mask($visaApplication->passport_number, '*', 3, -3) }}
</x-mail::panel>

## What Happens Next?

1. **Document Review** — Our team will verify all submitted documents within 1-2 business days.
2. **Agent Assignment** — A dedicated visa agent will be assigned to guide you through the process.
3. **Processing** — We will handle the visa application process on your behalf.
4. **Updates** — You will receive email notifications at each stage of the process.

If you have any questions or need to update your application, please don't hesitate to reach out via WhatsApp or email.

<x-mail::button url="https://wa.me/2348032775756" color="success">
Chat on WhatsApp
</x-mail::button>

Warm regards,<br>
**The {{ config('app.name') }} Visa Team**

<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.<br>
Your trusted China-Nigeria sourcing and visa services partner.<br>
<a href="mailto:support@elbildad.ng">support@elbildad.ng</a> · <a href="https://wa.me/2348032775756">+234 803 277 5756</a>
</x-mail::footer>
</x-slot:footer>
</x-mail::message>
