<?php

namespace App\Http\Controllers\Alumni;

use App\Http\Controllers\Controller;
use App\Models\AlumniUploadSession;
use App\Services\AlumniVideoStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PresignAlumniVideoController extends Controller
{
    public function __invoke(Request $request, AlumniVideoStorage $storage): JsonResponse
    {
        $validated = $request->validate([
            'file_name' => ['required', 'string', 'max:255'],
            'content_type' => ['required', 'string', 'in:'.implode(',', AlumniVideoStorage::CONTENT_TYPES)],
            'size' => ['required', 'integer', 'between:1,'.AlumniVideoStorage::MAX_SIZE],
        ]);

        $fileName = $validated['file_name'];
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if ($fileName !== basename($fileName)
            || preg_match('/[\\\\\/\\x00-\\x1F\\x7F]/u', $fileName)
            || ! array_key_exists($extension, AlumniVideoStorage::CONTENT_TYPES)
            || AlumniVideoStorage::CONTENT_TYPES[$extension] !== $validated['content_type']) {
            throw ValidationException::withMessages(['file_name' => 'Nama, ekstensi, atau MIME video tidak valid.']);
        }

        $objectKey = $storage->objectKey((string) config('alumni.periode'), $extension);
        $uploadToken = Str::random(64);
        $expiresAt = now()->addMinutes(5);
        $sessionBinding = $request->session()->get('alumni_upload_binding');

        if (! is_string($sessionBinding)) {
            $sessionBinding = Str::random(64);
            $request->session()->put('alumni_upload_binding', $sessionBinding);
        }

        AlumniUploadSession::query()->create([
            'session_hash' => hash('sha256', $sessionBinding),
            'token_hash' => hash('sha256', $uploadToken),
            'object_key' => $objectKey,
            'original_name' => $fileName,
            'extension' => $extension,
            'content_type' => $validated['content_type'],
            'expected_size' => $validated['size'],
            'expires_at' => $expiresAt,
        ]);

        $upload = $storage->presign($objectKey, $validated['content_type']);

        return response()->json([
            'upload_url' => $upload['url'],
            'object_key' => $objectKey,
            'content_type' => $validated['content_type'],
            'headers' => $upload['headers'],
            'upload_token' => $uploadToken,
            'expires_at' => $expiresAt->toIso8601String(),
        ]);
    }
}
