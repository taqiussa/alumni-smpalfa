<?php

namespace App\Models;

use Database\Factories\AlumniKurasiFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlumniKurasi extends Model
{
    /** @use HasFactory<AlumniKurasiFactory> */
    use HasFactory;

    protected $table = 'alumni_kurasi';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['diproses_at' => 'datetime'];
    }
}
