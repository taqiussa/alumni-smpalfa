<?php

namespace App\Models;

use Database\Factories\AlumniKriteriaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlumniKriteria extends Model
{
    /** @use HasFactory<AlumniKriteriaFactory> */
    use HasFactory;

    protected $table = 'alumni_kriteria';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['aktif' => 'boolean', 'bobot' => 'decimal:2'];
    }
}
