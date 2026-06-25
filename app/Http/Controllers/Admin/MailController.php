<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class MailController extends Controller
{
    public function index()
    {
        $customers = User::role('customer')->select('id', 'name', 'email')->get();
        return Inertia::render('Admin/Mail/Index', [
            'customers' => $customers
        ]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $user = User::findOrFail($request->customer_id);

        try {
            Mail::html($request->message, function ($message) use ($user, $request) {
                $message->to($user->email)
                        ->subject($request->subject);
            });

            return back()->with('success', 'Mail sent successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send mail: ' . $e->getMessage());
        }
    }
}
