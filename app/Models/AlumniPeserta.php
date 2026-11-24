<?php

namespace App\Models;

use Database\Factories\AlumniPesertaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AlumniPeserta extends Model
{
    /** @use HasFactory<AlumniPesertaFactory> */
    use HasFactory;

    protected $table = 'alumni_peserta';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['registered_at' => 'datetime'];
    }

    public function videos(): HasMany
    {
        return $this->hasMany(AlumniVideo::class, 'peserta_id');
    }

    public function kurasi(): HasMany
    {
        return $this->hasMany(AlumniKurasi::class, 'peserta_id');
    }

    public function revisi(): HasMany
    {
        return $this->hasMany(AlumniRevisi::class, 'peserta_id');
    }

    public function publikasi(): HasOne
    {
        return $this->hasOne(AlumniPublikasi::class, 'peserta_id');
    }

    public function penilaian(): HasMany
    {
        return $this->hasMany(AlumniPenilaian::class, 'peserta_id');
    }
}
