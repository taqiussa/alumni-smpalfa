<?php

namespace App\Services;

use App\Models\AlumniPenilaian;
use Illuminate\Support\Collection;

class AlumniScoringService
{
    /**
     * @param  Collection<int, AlumniPenilaian>  $scores
     */
    public function weightedTotal(Collection $scores): ?float
    {
        $weightedScores = $scores->filter(fn ($score): bool => $score->kriteria !== null);
        $weight = (float) $weightedScores->sum(fn ($score): float => (float) $score->kriteria->bobot);

        if ($weight <= 0) {
            return null;
        }

        return round((float) $weightedScores->sum(fn ($score): float => (float) $score->nilai * (float) $score->kriteria->bobot) / $weight, 2);
    }
}
