<?php

namespace App\Enums;

enum RfqStatusEnum: string
{
    case PENDING = 'pending';
    case ASSIGNED = 'assigned';
    case SOURCING = 'sourcing';
    case PURCHASED = 'purchased';
    case SHIPPED = 'shipped';
    case COMPLETED = 'completed';
    case QUEUED = 'queued';
    case NOT_FOUND = 'not_found';
    case QUOTED = 'quoted';
    case AWAITING_PAYMENT = 'awaiting_payment';
    case PAYMENT_RECEIVED = 'payment_received';
}
