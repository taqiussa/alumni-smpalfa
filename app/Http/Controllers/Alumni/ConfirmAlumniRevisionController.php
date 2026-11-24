<?php

namespace App\Http\Controllers\Alumni;

use App\Http\Controllers\Controller;
use App\Models\AlumniPeserta;
use App\Models\AlumniUploadSession;
use App\Models\AlumniVideo;
use App\Services\AlumniVideoStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ConfirmAlumniRevisionController extends Controller
{
    public function __invoke(Request $request, AlumniVideoStorage $storage): JsonResponse
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:24'],
            'token' => ['required', 'string', 'size:64'],
            'upload_token' => ['required', 'string', 'size:64'],
            'object_key' => ['required', 'string', 'max:512'],
        ]);

        try {
            DB::transaction(function () use ($request, $validated, $storage): void {
                $peserta = AlumniPeserta::query()->where('kode_registrasi', $validated['kode'])->lockForUpdate()->firstOrFail();
                abort_unless(hash_equals($peserta->revision_token, $validated['token']) && $peserta->status_kurasi === 'revisi', 404);
                $binding = $request->session()->get('alumni_upload_binding');
                $upload = AlumniUploadSession::query()->where('token_hash', hash('sha256', $validated['upload_token']))->where('object_key', $validated['object_key'])->lockForUpdate()->first();

                if (! $upload || ! is_string($binding) || ! hash_equals($upload->session_hash, hash('sha256', $binding)) || $upload->status !== 'presigned' || $upload->expires_at->isPast()) {
                    throw ValidationException::withMessages(['upload_token' => 'Sesi upload revisi tidak valid.']);
                }

                $metadata = $storage->verify($upload);
                $version = (int) $peserta->videos()->max('versi') + 1;
                $peserta->videos()->where('aktif', true)->update(['aktif' => false]);
                AlumniVideo::query()->create([...$metadata, 'peserta_id' => $peserta->id, 'versi' => $version, 'aktif' => true, 'uploaded_at' => now()]);
                $peserta->revisi()->whereNull('dipenuhi_at')->update(['dipenuhi_at' => now()]);
                $peserta->update(['status_kurasi' => 'menunggu', 'status_publikasi' => 'belum', 'status_penilaian' => 'belum']);
                $upload->update(['status' => 'completed', 'completed_at' => now()]);
            }, 3);
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages(['object_key' => $exception->getMessage()]);
        }

        return response()->json(['message' => 'Revisi berhasil dikirim.']);
    }
}
