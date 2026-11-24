<?php

namespace App\Models;

use Database\Factories\AlumniRevisiFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlumniRevisi extends Model
{
    /** @use HasFactory<AlumniRevisiFactory> */
    use HasFactory;

    protected $table = 'alumni_revisi';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['batas_revisi_at' => 'datetime', 'dipenuhi_at' => 'datetime'];
    }
}
