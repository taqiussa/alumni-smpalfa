<?php

namespace App\Models;

use Database\Factories\AlumniVideoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlumniVideo extends Model
{
    /** @use HasFactory<AlumniVideoFactory> */
    use HasFactory;

    protected $table = 'alumni_video';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['aktif' => 'boolean', 'uploaded_at' => 'datetime'];
    }

    public function peserta(): BelongsTo
    {
        return $this->belongsTo(AlumniPeserta::class, 'peserta_id');
    }
}
