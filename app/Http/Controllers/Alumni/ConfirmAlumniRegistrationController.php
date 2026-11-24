<?php

namespace App\Http\Controllers\Alumni;

use App\Http\Controllers\Controller;
use App\Models\AlumniUploadSession;
use App\Services\AlumniRegistrationService;
use App\Services\AlumniVideoStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class ConfirmAlumniRegistrationController extends Controller
{
    public function __invoke(Request $request, AlumniRegistrationService $registration, AlumniVideoStorage $storage): JsonResponse
    {
        $validated = $request->validate([
            ...AlumniRegistrationService::registrationRules(),
            'upload_token' => ['required', 'string', 'size:64'],
            'object_key' => ['required', 'string', 'max:512'],
        ]);

        $upload = null;

        try {
            $peserta = DB::transaction(function () use ($request, $validated, $registration, $storage, &$upload) {
                $upload = AlumniUploadSession::query()->where('token_hash', hash('sha256', $validated['upload_token']))->where('object_key', $validated['object_key'])->lockForUpdate()->first();

                $sessionBinding = $request->session()->get('alumni_upload_binding');

                if (! $upload || ! is_string($sessionBinding) || ! hash_equals($upload->session_hash, hash('sha256', $sessionBinding))) {
                    throw ValidationException::withMessages(['upload_token' => 'Token upload tidak valid.']);
                }

                if ($upload->status !== 'presigned') {
                    throw ValidationException::withMessages(['upload_token' => 'Upload ini sudah tidak dapat digunakan.']);
                }

                if ($upload->expires_at->isPast()) {
                    throw ValidationException::withMessages(['upload_token' => 'Masa berlaku upload telah berakhir.']);
                }

                $metadata = $storage->verify($upload);
                $peserta = $registration->register(collect($validated)->except(['upload_token', 'object_key', 'keaslian', 'persetujuan_publikasi', 'patuh_ketentuan'])->all(), $upload, $metadata);
                $upload->forceFill(['status' => 'completed', 'completed_at' => now()])->save();

                return $peserta;
            }, 3);
        } catch (Throwable $exception) {
            if ($upload instanceof AlumniUploadSession && $upload->status === 'presigned') {
                $storage->delete($upload->object_key);
                AlumniUploadSession::query()->whereKey($upload->id)->where('status', 'presigned')->update(['status' => 'failed', 'updated_at' => now()]);
            }

            if ($exception instanceof RuntimeException) {
                throw ValidationException::withMessages(['object_key' => $exception->getMessage()]);
            }

            throw $exception;
        }

        return response()->json(['redirect_url' => route('alumni.success', ['kode' => $peserta->kode_registrasi]), 'registration_code' => $peserta->kode_registrasi], 201);
    }
}
