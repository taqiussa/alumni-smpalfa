<?php

namespace App\Services;

use App\Models\AlumniUploadSession;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class AlumniVideoStorage
{
    public const MAX_SIZE = 104_857_600;

    /** @var array<string, string> */
    public const CONTENT_TYPES = [
        'mp4' => 'video/mp4',
        'mov' => 'video/quicktime',
    ];

    /** @return array{url:string,headers:array<string,string>} */
    public function presign(string $objectKey, string $contentType): array
    {
        $upload = Storage::disk(config('alumni.disk_private'))->temporaryUploadUrl(
            $objectKey,
            now()->addMinutes(5),
            ['ContentType' => $contentType],
        );

        return [
            'url' => $upload['url'],
            'headers' => ['Content-Type' => $contentType],
        ];
    }

    public function objectKey(string $periode, string $extension): string
    {
        return sprintf(
            'alumni-bercerita/%s/original/%s.%s',
            $this->sanitizePeriod($periode),
            Str::uuid(),
            $extension,
        );
    }

    /** @return array{disk:string,object_key:string,original_name:string,mime_type:string,ukuran:int} */
    public function verify(AlumniUploadSession $upload): array
    {
        $disk = Storage::disk(config('alumni.disk_private'));

        if (! $disk->exists($upload->object_key)) {
            throw new RuntimeException('Video belum ditemukan di penyimpanan.');
        }

        // The S3 adapter resolves both fields with HeadObject; the original file never traverses PHP.
        $size = $disk->size($upload->object_key);
        $mimeType = $disk->mimeType($upload->object_key);

        if ($size !== $upload->expected_size || $mimeType !== $upload->content_type) {
            throw new RuntimeException('Metadata video di penyimpanan tidak sesuai.');
        }

        $stream = $disk->readStream($upload->object_key);
        $signature = is_resource($stream) ? fread($stream, 4096) : false;

        if (is_resource($stream)) {
            fclose($stream);
        }

        if (! is_string($signature) || ! $this->hasExpectedIsoBaseMediaSignature($signature, $upload->extension)) {
            throw new RuntimeException('Isi file bukan video MP4 atau MOV yang valid.');
        }

        return [
            'disk' => config('alumni.disk_private'),
            'object_key' => $upload->object_key,
            'original_name' => $upload->original_name,
            'mime_type' => $mimeType,
            'ukuran' => $size,
        ];
    }

    public function delete(string $objectKey): void
    {
        Storage::disk(config('alumni.disk_private'))->delete($objectKey);
    }

    private function sanitizePeriod(string $periode): string
    {
        $sanitized = trim((string) preg_replace('/[^\\pL\\pN_-]+/u', '-', $periode), '-');

        if ($sanitized === '') {
            throw new RuntimeException('Periode penyimpanan tidak valid.');
        }

        return Str::limit($sanitized, 20, '');
    }

    private function hasExpectedIsoBaseMediaSignature(string $bytes, string $extension): bool
    {
        $offset = 0;
        $length = strlen($bytes);

        while ($offset + 16 <= $length) {
            $size = unpack('N', substr($bytes, $offset, 4))[1];
            $type = substr($bytes, $offset + 4, 4);

            if ($type === 'ftyp') {
                $brand = substr($bytes, $offset + 8, 4);

                return $extension === 'mov'
                    ? $brand === 'qt  '
                    : in_array($brand, ['isom', 'iso2', 'avc1', 'mp41', 'mp42', 'M4V ', 'dash', 'MSNV'], true);
            }

            if ($size < 8) {
                return false;
            }

            $offset += $size;
        }

        return false;
    }
}
