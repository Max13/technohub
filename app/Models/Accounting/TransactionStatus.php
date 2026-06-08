<?php

namespace App\Models\Accounting;

enum TransactionStatus : string
{
    case OK = 'OK';
    case MISSED = 'Missed';
}
