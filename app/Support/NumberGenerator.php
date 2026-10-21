<?php

namespace App\Support;

use App\Models\System\NumberSequence;
use Illuminate\Support\Facades\DB;

class NumberGenerator
{
    /**
     * Generate a unique sequential number with atomic lock.
     */
    public static function generate(string $code, string $name, string $prefix, int $numberLength = 6, bool $yearReset = true, bool $monthReset = false): string
    {
        return DB::transaction(function () use ($code, $name, $prefix, $numberLength, $yearReset, $monthReset) {
            $sequence = NumberSequence::where('code', $code)
                ->lockForUpdate()
                ->first();

            if (!$sequence) {
                $sequence = NumberSequence::create([
                    'code' => $code,
                    'name' => $name,
                    'prefix' => $prefix,
                    'number_length' => $numberLength,
                    'current_number' => 1,
                    'year_reset' => $yearReset,
                    'month_reset' => $monthReset,
                    'active' => true,
                ]);
            }

            // 检查是否需要年度/月度重置
            $now = now();
            if (($yearReset && $now->year !== (int)$sequence->last_reset_year) ||
                ($monthReset && $now->month !== (int)($sequence->last_reset_month ?? 0))) {
                $sequence->current_number = 1;
                $sequence->last_reset_year = $now->year;
                $sequence->last_reset_month = $now->month;
            }

            $currentNumber = $sequence->current_number;
            $sequence->current_number++;
            $sequence->save();

            $year = $now->format('Y');
            $number = str_pad($currentNumber, $numberLength, '0', STR_PAD_LEFT);

            if ($monthReset) {
                $month = $now->format('m');
                return "{$prefix}-{$year}-{$month}-{$number}";
            }

            return "{$prefix}-{$year}-{$number}";
        });
    }
}
