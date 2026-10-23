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

    public function label(): string
    {
        return match ($this) {
            self::IQC => 'IQC 来料检验',
            self::IPQC => 'IPQC 过程检验',
            self::FQC => 'FQC 成品检验',
            self::OQC => 'OQC 出货检验',
            self::Rework => '返工检验',
            self::Final => '最终检验',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
