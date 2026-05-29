<?php

namespace App\Enums;

enum EstimateStatusEnum: string
{
    case DRAFT = 'draft';
    case SENT = 'sent';
    case ACCEPTED = 'accepted';
    case DECLINED = 'declined';
}
