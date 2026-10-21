<?php

namespace App\Enums;

enum DefectCode: string
{
    case Appearance = 'appearance';
    case Dimension = 'dimension';
    case Electrical = 'electrical';
    case Soldering = 'soldering';
    case Assembly = 'assembly';
    case Material = 'material';
    case Function = 'function';
    case Packaging = 'packaging';
}
