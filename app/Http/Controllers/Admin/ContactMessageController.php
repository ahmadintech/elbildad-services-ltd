<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class ContactMessageController extends Controller
{
    public function index()
    {
        $messages = ContactMessage::latest()->get();
        return Inertia::render('Admin/ContactMessages/Index', [
            'messages' => $messages
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        ContactMessage::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Your message has been sent successfully.']);
        }
        return back()->with('success', 'Your message has been sent successfully.');
    }

    public function markAsRead(ContactMessage $contactMessage)
    {
        $contactMessage->update(['is_read' => true]);
        return back();
    }

    public function reply(Request $request, ContactMessage $contactMessage)
    {
        $request->validate([
            'reply_message' => 'required|string',
        ]);

        try {
            Mail::html($request->reply_message, function ($message) use ($contactMessage) {
                $message->to($contactMessage->email)
                        ->subject('Re: ' . ($contactMessage->subject ?? 'Your Contact Message'));
            });

            $contactMessage->update([
                'is_read' => true,
                'replied_at' => now(),
            ]);

            return back()->with('success', 'Reply sent successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send reply: ' . $e->getMessage());
        }
    }

    public function destroy(ContactMessage $contactMessage)
    {
        $contactMessage->delete();
        return back()->with('success', 'Message deleted.');
    }
}
