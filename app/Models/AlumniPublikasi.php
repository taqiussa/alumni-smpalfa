<?php

namespace App\Models;

use Database\Factories\AlumniPublikasiFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlumniPublikasi extends Model
{
    /** @use HasFactory<AlumniPublikasiFactory> */
    use HasFactory;

    protected $table = 'alumni_publikasi';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['dijadwalkan_at' => 'datetime', 'dipublikasikan_at' => 'datetime'];
    }
}
