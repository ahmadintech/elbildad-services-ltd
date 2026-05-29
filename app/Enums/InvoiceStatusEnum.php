<?php

namespace App\Enums;

enum InvoiceStatusEnum: string
{
    case DRAFT = 'draft';
    case SENT = 'sent';
    case PAID = 'paid';
    case PARTIALLY_PAID = 'partially_paid';
    case OVERDUE = 'overdue';
    case VOID = 'void';
}
