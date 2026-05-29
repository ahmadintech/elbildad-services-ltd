<x-mail::message>
# Welcome to Elbildad Services, {{ $user->name }}!

We are absolutely thrilled to have you with us. A secure account has been created for you so you can easily track the progress of your RFQs (Request for Quotation) and communicate directly with our sourcing agents.

Here are your account credentials. Please keep them secure:

<x-mail::panel>
**Email / WhatsApp:** {{ $user->email ?? $user->whatsapp_number }}  
**Password:** {{ $password }}
</x-mail::panel>

You can log in to your dashboard at any time to view the status of your requests, view invoices, and manage your profile.

<x-mail::button :url="route('login')" color="primary">
Access Your Dashboard
</x-mail::button>

<p class="sub">For security reasons, we highly recommend changing your password immediately after your first login.</p>

If you have any questions, our dedicated support team is always ready to help!

Warm regards,<br>
**The {{ config('app.name') }} Team**

<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.<br>
Providing premium sourcing and logistics solutions.<br>
<a href="mailto:support@elbildadservices.ng">support@elbildadservices.ng</a>
</x-mail::footer>
</x-slot:footer>
</x-mail::message>
