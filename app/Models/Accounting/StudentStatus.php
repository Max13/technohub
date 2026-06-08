<?php

namespace App\Models\Accounting;

enum StudentStatus : string
{
    case OK = 'OK';
    case VISA_PENDING = 'VisaPending';
    case REFUSED = 'Refused';
    case ELEARNING = 'E-Learning';
    case CANCELED = 'Cancelled';
    case TRANSFERRED_AWAY = 'TransferredAway';
    case UNKNOWN = 'Unknown';
}
