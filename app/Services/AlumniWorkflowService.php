<?php

namespace App\Services;

use App\Models\AlumniAuditLog;
use App\Models\AlumniKriteria;
use App\Models\AlumniKurasi;
use App\Models\AlumniPenilaian;
use App\Models\AlumniPeserta;
use App\Models\AlumniPublikasi;
use App\Models\AlumniRevisi;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AlumniWorkflowService
{
    /** @param array<int, float|int|string> $scores */
    public function score(AlumniPeserta $peserta, int $juriId, array $scores): void
    {
        if ($peserta->status_kurasi !== 'lolos') {
            throw ValidationException::withMessages(['score' => 'Hanya karya yang lolos kurasi dapat dinilai.']);
        }

        $criteria = AlumniKriteria::query()->where('periode', $peserta->periode)->where('aktif', true)->orderBy('urutan')->get();

        if ($criteria->isEmpty()) {
            throw ValidationException::withMessages(['score' => 'Kriteria penilaian untuk periode ini belum tersedia.']);
        }

        DB::transaction(function () use ($criteria, $juriId, $peserta, $scores): void {
            foreach ($criteria as $criterion) {
                $value = $scores[$criterion->id] ?? null;

                if (! is_numeric($value) || (float) $value < 0 || (float) $value > 100) {
                    throw ValidationException::withMessages(["scores.{$criterion->id}" => 'Nilai harus berada antara 0 sampai 100.']);
                }

                AlumniPenilaian::query()->updateOrCreate(
                    ['peserta_id' => $peserta->id, 'kriteria_id' => $criterion->id, 'juri_id' => $juriId],
                    ['nilai' => $value, 'final' => true, 'finalized_at' => now()],
                );
            }

            $peserta->update(['status_penilaian' => 'sudah']);
            $this->audit('alumni.scored', $peserta, $juriId, [], ['status_penilaian' => 'sudah']);
        }, 3);
    }

    public function curate(AlumniPeserta $peserta, string $status, ?string $catatan, int $userId): void
    {
        if (! in_array($status, ['lolos', 'ditolak', 'revisi'], true)) {
            throw ValidationException::withMessages(['status' => 'Status kurasi tidak valid.']);
        }

        if (! in_array($peserta->status_kurasi, ['menunggu', 'revisi'], true)) {
            throw ValidationException::withMessages(['status' => 'Karya ini sudah melalui proses kurasi akhir.']);
        }

        if (in_array($status, ['ditolak', 'revisi'], true) && blank($catatan)) {
            throw ValidationException::withMessages(['catatan' => 'Catatan kurator wajib diisi untuk penolakan atau revisi.']);
        }

        DB::transaction(function () use ($peserta, $status, $catatan, $userId): void {
            $before = $peserta->status_kurasi;
            $peserta->update(['status_kurasi' => $status]);
            AlumniKurasi::query()->create([
                'peserta_id' => $peserta->id,
                'user_id' => $userId,
                'status_sebelum' => $before,
                'status_sesudah' => $status,
                'catatan' => $catatan,
                'diproses_at' => now(),
            ]);

            if ($status === 'revisi') {
                AlumniRevisi::query()->create([
                    'peserta_id' => $peserta->id,
                    'diminta_oleh' => $userId,
                    'catatan' => $catatan,
                ]);
            }

            $this->audit('alumni.curated', $peserta, $userId, ['status_kurasi' => $before], ['status_kurasi' => $status]);
        }, 3);
    }

    public function publish(AlumniPeserta $peserta, int $userId, ?string $platform, ?string $url): void
    {
        if ($peserta->status_kurasi !== 'lolos') {
            throw ValidationException::withMessages(['publication' => 'Hanya karya yang lolos kurasi dapat dipublikasikan.']);
        }

        DB::transaction(function () use ($peserta, $userId, $platform, $url): void {
            AlumniPublikasi::query()->updateOrCreate(['peserta_id' => $peserta->id], [
                'user_id' => $userId,
                'status' => 'dipublikasikan',
                'platform' => $platform,
                'url_posting' => $url,
                'dipublikasikan_at' => now(),
            ]);
            $peserta->update(['status_publikasi' => 'sudah']);
            $this->audit('alumni.published', $peserta, $userId, [], ['status_publikasi' => 'sudah']);
        }, 3);
    }

    public function unpublish(AlumniPeserta $peserta, int $userId): void
    {
        if ($peserta->status_publikasi !== 'sudah') {
            throw ValidationException::withMessages(['publication' => 'Karya ini belum dipublikasikan.']);
        }

        DB::transaction(function () use ($peserta, $userId): void {
            $peserta->publikasi()->update(['status' => 'dibatalkan', 'dipublikasikan_at' => null]);
            $peserta->update(['status_publikasi' => 'belum']);
            $this->audit('alumni.unpublished', $peserta, $userId, ['status_publikasi' => 'sudah'], ['status_publikasi' => 'belum']);
        }, 3);
    }

    /** @param array<string, mixed> $before @param array<string, mixed> $after */
    private function audit(string $event, AlumniPeserta $peserta, int $userId, array $before, array $after): void
    {
        AlumniAuditLog::query()->create([
            'user_id' => $userId,
            'event' => $event,
            'auditable_type' => AlumniPeserta::class,
            'auditable_id' => $peserta->id,
            'before' => $before,
            'after' => $after,
            'ip_address' => request()->ip(),
        ]);
    }
}
