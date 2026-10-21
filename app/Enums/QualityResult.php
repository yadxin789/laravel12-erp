<?php

namespace App\Enums;

enum QualityResult: string
{
    case Pass = 'pass';
    case Fail = 'fail';
    case Conditional = 'conditional';
    case Waived = 'waived';
}
