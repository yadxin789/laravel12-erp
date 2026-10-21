<?php

namespace App\Support;

class Money
{
    const MINOR_UNIT = 100;

    public static function toMinor(float $amount): int
    {
        return (int) round($amount * self::MINOR_UNIT);
    }

    public static function fromMinor(int $amount): float
    {
        return $amount / self::MINOR_UNIT;
    }

    public static function format(float $amount, int $decimals = 2): string
    {
        return number_format($amount, $decimals, '.', ',');
    }

    public static function add(float $a, float $b): float
    {
        return self::fromMinor(self::toMinor($a) + self::toMinor($b));
    }

    public static function subtract(float $a, float $b): float
    {
        return self::fromMinor(self::toMinor($a) - self::toMinor($b));
    }

    public static function multiply(float $a, float $b): float
    {
        return self::fromMinor(self::toMinor($a) * $b);
    }

    public static function divide(float $a, float $b): float
    {
        if ($b === 0) {
            return 0;
        }
        return $a / $b;
    }
}
