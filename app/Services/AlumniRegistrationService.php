<?php

namespace App\Services;

use App\Models\AlumniPeserta;
use App\Models\AlumniUploadSession;
use App\Models\AlumniVideo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AlumniRegistrationService
{
    /** @return array<string, array<int, mixed>> */
    public static function registrationRules(): array
    {
        return [
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'tahun_kelulusan' => ['required', 'integer', 'between:1950,'.now()->year],
            'whatsapp' => ['required', 'string', 'max:30'],
            'domisili' => ['required', 'string', 'max:150'],
            'status' => ['required', 'in:Pelajar,Mahasiswa,Bekerja,Lainnya'],
            'platform_sosial' => ['required', 'in:instagram,tiktok,keduanya'],
            'username_instagram' => ['nullable', 'string', 'max:255'],
            'link_instagram' => ['nullable', 'url', 'max:2048'],
            'username_tiktok' => ['nullable', 'string', 'max:255'],
            'link_tiktok' => ['nullable', 'url', 'max:2048'],
            'keaslian' => ['accepted'],
            'persetujuan_publikasi' => ['accepted'],
            'patuh_ketentuan' => ['accepted'],
        ];
    }

    /** @param array<string, mixed> $data */
    public function register(array $data, AlumniUploadSession $upload, array $metadata): AlumniPeserta
    {
        return DB::transaction(function () use ($data, $metadata): AlumniPeserta {
            $peserta = AlumniPeserta::create([...$data, 'periode' => config('alumni.periode'), 'kode_registrasi' => $this->registrationCode(), 'revision_token' => Str::random(64), 'registered_at' => now()]);
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
