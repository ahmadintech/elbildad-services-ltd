<?php

namespace App\Services\Mail;

use App\Models\Rfq;
use App\Mail\RfqSummaryMail;
use Illuminate\Support\Facades\Mail;

class RfqMailService
{
    public function sendRfqSummaryToCustomer(Rfq $rfq): void
    {
        $rfq->loadMissing('customer');

        if (!$rfq->customer || empty($rfq->customer->email)) {
            return;
        }

        Mail::to($rfq->customer->email)->queue(new RfqSummaryMail($rfq));
    }
}
