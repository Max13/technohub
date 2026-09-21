<?php

namespace App\Models\Accounting;

enum TransactionOrigin : string
{
    case BANK = 'Bank';
    case EXCEL = 'Excel';
    case MANUAL = 'Manual';
}
