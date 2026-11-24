<?php

namespace App\Models;

use Database\Factories\AlumniPenilaianFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlumniPenilaian extends Model
{
    /** @use HasFactory<AlumniPenilaianFactory> */
    use HasFactory;

    protected $table = 'alumni_penilaian';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['nilai' => 'decimal:2', 'final' => 'boolean', 'finalized_at' => 'datetime'];
    }
}
