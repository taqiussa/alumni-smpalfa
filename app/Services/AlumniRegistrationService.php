<?php

namespace App\Services;

use App\Models\AlumniPeserta;
use App\Models\AlumniVideo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AlumniRegistrationService
{
    public function __construct(private AlumniVideoStorage $storage) {}

    /** @param array<string, mixed> $data */
    public function register(array $data, UploadedFile $video): AlumniPeserta
    {
        return DB::transaction(function () use ($data, $video): AlumniPeserta {
            $peserta = AlumniPeserta::create([...$data, 'periode' => config('alumni.periode'), 'kode_registrasi' => $this->registrationCode(), 'revision_token' => Str::random(64), 'registered_at' => now()]);
            $metadata = $this->storage->storeOriginal($video, config('alumni.periode'));
            AlumniVideo::create([...$metadata, 'peserta_id' => $peserta->id, 'versi' => 1]);

            return $peserta;
        }, 3);
    }

    private function registrationCode(): string
    {
        do {
            $code = 'AB-'.now()->format('ymd').'-'.Str::upper(Str::random(6));
        } while (AlumniPeserta::where('kode_registrasi', $code)->exists());

        return $code;
    }
}
