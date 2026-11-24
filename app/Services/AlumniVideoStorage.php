<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AlumniVideoStorage
{
    /** @return array{disk:string,object_key:string,original_name:string,mime_type:string,ukuran:int} */
    public function storeOriginal(UploadedFile $file, string $periode): array
    {
        $key = sprintf('alumni-bercerita/%s/original/%s.%s', $periode, Str::uuid(), $file->extension());
        Storage::disk(config('alumni.disk_private'))->putFileAs(dirname($key), $file, basename($key), ['visibility' => 'private']);

        return ['disk' => config('alumni.disk_private'), 'object_key' => $key, 'original_name' => $file->getClientOriginalName(), 'mime_type' => (string) $file->getMimeType(), 'ukuran' => $file->getSize()];
    }

    public function temporaryUrl(string $key): string
    {
        return Storage::disk(config('alumni.disk_private'))->temporaryUrl($key, now()->addMinutes(10));
    }
}
