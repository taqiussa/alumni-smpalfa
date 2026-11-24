<?php

namespace App\Models;

use Database\Factories\AlumniPengaturanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlumniPengaturan extends Model
{
    /** @use HasFactory<AlumniPengaturanFactory> */
    use HasFactory;

    protected $table = 'alumni_pengaturan';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['pendaftaran_dibuka' => 'boolean', 'pendaftaran_mulai_at' => 'datetime', 'pendaftaran_tutup_at' => 'datetime', 'revisi_tutup_at' => 'datetime', 'platform_wajib' => 'array', 'pengaturan_penilaian' => 'array'];
    }
}
