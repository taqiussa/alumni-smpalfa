<?php

namespace App\Http\Controllers\Alumni;

use App\Http\Controllers\Controller;
use App\Models\AlumniUploadSession;
use App\Services\AlumniVideoStorage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CancelAlumniVideoUploadController extends Controller
{
    public function __invoke(Request $request, AlumniVideoStorage $storage): Response
    {
        $validated = $request->validate(['upload_token' => ['required', 'string', 'size:64'], 'object_key' => ['required', 'string', 'max:512']]);

        DB::transaction(function () use ($request, $storage, $validated): void {
            $upload = AlumniUploadSession::query()->where('token_hash', hash('sha256', $validated['upload_token']))->where('object_key', $validated['object_key'])->lockForUpdate()->first();

            $sessionBinding = $request->session()->get('alumni_upload_binding');

            if (! $upload || ! is_string($sessionBinding) || ! hash_equals($upload->session_hash, hash('sha256', $sessionBinding))) {
                throw ValidationException::withMessages(['upload_token' => 'Token upload tidak valid.']);
            }

            if ($upload->status === 'completed' || $upload->status === 'cancelled') {
                return;
            }

            if (! $upload->video()->exists()) {
                $storage->delete($upload->object_key);
            }

            $upload->update(['status' => 'cancelled']);
        });

        return response()->noContent();
    }
}
