<?php

namespace App\Enums;

enum InspectionType: string
{
    case IQC = 'iqc';
    case IPQC = 'ipqc';
    case FQC = 'fqc';
    case OQC = 'oqc';
    case Rework = 'rework';
    case Final = 'final';
}
