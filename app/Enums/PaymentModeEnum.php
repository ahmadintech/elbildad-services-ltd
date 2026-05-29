<?php

namespace App\Enums;

enum PaymentModeEnum: string
{
    case CASH = 'cash';
    case BANK_TRANSFER = 'bank_transfer';
    case CARD = 'card';
    case USDT = 'usdt';
    case OTHER = 'other';
}
