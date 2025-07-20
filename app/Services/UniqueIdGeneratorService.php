<?php

namespace App\Services;

use App\Models\Sequence;
use Illuminate\Support\Facades\DB;

class UniqueIdGeneratorService
{
    public function generate(string $prefix, int $year): string
    {
        return DB::transaction(function () use ($prefix, $year) {
            $sequence = Sequence::firstOrCreate(
                ['name' => $prefix, 'year' => $year],
                ['value' => 0]
            );

            $sequence->value++;
            $sequence->save();

            return sprintf('%s-%d-%04d', $prefix, $year, $sequence->value);
        });
    }
}
