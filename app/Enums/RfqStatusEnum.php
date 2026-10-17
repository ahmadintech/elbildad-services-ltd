<?php

namespace App\Enums;

enum RfqStatusEnum: string
{
    // ── Pre-processing ────────────────────────────────────────────────────────
    case COMMITMENT_FEE_PENDING = 'commitment_fee_pending'; // RFQ submitted, awaiting Zainpay commitment fee

    // ── Active pipeline ───────────────────────────────────────────────────────
    case PENDING   = 'pending';   // Commitment fee paid — agent pipeline starts
    case QUEUED    = 'queued';    // AI sourcing job queued
    case ASSIGNED  = 'assigned';  // Agent assigned
    case SOURCING  = 'sourcing';  // Agent actively sourcing the product
    case PURCHASED = 'purchased'; // Product purchased from supplier
    case SHIPPED   = 'shipped';   // Product shipped to customer
    case COMPLETED = 'completed'; // Fully delivered

    // ── Quotation & final payment (for the actual product price) ──────────────
    case QUOTED            = 'quoted';            // Agent has sent a price quote
    case AWAITING_PAYMENT  = 'awaiting_payment';  // Customer needs to pay the quoted product price
    case PAYMENT_RECEIVED  = 'payment_received';  // Full product payment received

    // ── Terminal ──────────────────────────────────────────────────────────────
    case NOT_FOUND = 'not_found'; // Agent could not source the product
}
