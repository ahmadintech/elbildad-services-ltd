<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    /**
     * Display a listing of the user's payments.
     */
    public function index(Request $request): Response
    {
        $transactions = Transaction::where('user_id', $request->user()->id)
            ->with(['rfq:id,product_name,tracking_token'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'reference' => $transaction->reference,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency,
                    'status' => $transaction->status,
                    'gateway' => $transaction->gateway,
                    'created_at_formatted' => $transaction->created_at->format('M d, Y h:i A'),
                    'payment_for' => $transaction->rfq ? 'RFQ: ' . $transaction->rfq->product_name : 'General Payment',
                    'tracking_token' => $transaction->rfq ? $transaction->rfq->tracking_token : null,
                ];
            });

        return Inertia::render('Customer/Payments/Index', [
            'transactions' => $transactions
        ]);
    }

    /**
     * Display a listing of all payments for Admin.
     */
    public function adminIndex(): Response
    {
        $transactions = Transaction::with(['user:id,name,email', 'rfq:id,product_name,tracking_token'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'reference' => $transaction->reference,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency,
                    'status' => $transaction->status,
                    'gateway' => $transaction->gateway,
                    'created_at_formatted' => $transaction->created_at->format('M d, Y h:i A'),
                    'user' => $transaction->user ? $transaction->user->name . ' (' . $transaction->user->email . ')' : 'Unknown',
                    'payment_for' => $transaction->rfq ? 'RFQ: ' . $transaction->rfq->product_name : 'General Payment',
                ];
            });

        $totalRevenue = Transaction::where('status', 'successful')->sum('amount');
        $successfulCount = Transaction::where('status', 'successful')->count();
        $pendingCount = Transaction::where('status', 'pending')->count();

        $stats = [
            ['title' => 'Total Commitment Revenue', 'value' => '₦' . number_format($totalRevenue, 2), 'color' => 'green'],
            ['title' => 'Successful Payments', 'value' => $successfulCount, 'color' => 'blue'],
            ['title' => 'Pending Payments', 'value' => $pendingCount, 'color' => 'orange'],
        ];

        return Inertia::render('Admin/Payments/Index', [
            'transactions' => $transactions,
            'stats' => $stats
        ]);
    }
}
