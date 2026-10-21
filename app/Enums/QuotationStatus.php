<?php

namespace App\Enums;

enum QuotationStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Won = 'won';
    case Lost = 'lost';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
}
