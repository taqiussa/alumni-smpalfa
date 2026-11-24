<?php

use App\Services\AlumniVideoStorage;
use Illuminate\Support\Str;
use Mockery\MockInterface;

function registrationPayload(array $overrides = []): array
{
    return [...[
        'nama_lengkap' => 'Siti Alumni',
        'tahun_kelulusan' => 2018,
        'whatsapp' => '081234567890',
        'domisili' => 'Bandung',
        'status' => 'Bekerja',
        'platform_sosial' => 'instagram',
        'keaslian' => '1',
        'persetujuan_publikasi' => '1',
        'patuh_ketentuan' => '1',
    ], ...$overrides];
}

function fakeVideoStorage(): MockInterface
{
    $storage = Mockery::mock(AlumniVideoStorage::class);
    app()->instance(AlumniVideoStorage::class, $storage);

    return $storage;
}

function presignedUpload(MockInterface $storage): array
{
    $objectKey = 'alumni-bercerita/2027-2028/original/'.Str::uuid().'.mp4';
    $storage->shouldReceive('objectKey')->once()->andReturn($objectKey);
    $storage->shouldReceive('presign')->once()->andReturn(['url' => 'https://s3.example.test/upload', 'headers' => ['Content-Type' => 'video/mp4']]);

    return test()->withSession(['alumni_upload_binding' => 'testing-upload-session'])->postJson(route('alumni.registration.presign'), [
        'file_name' => 'cerita.mp4',
        'content_type' => 'video/mp4',
        'size' => 1024,
    ])->assertOk()->json();
}

function confirmUpload(array $payload)
{
    return test()->withSession(['alumni_upload_binding' => 'testing-upload-session'])->postJson(route('alumni.registration.confirm'), $payload);
}

it('returns a five-minute presigned private upload contract for valid metadata', function () {
    $storage = fakeVideoStorage();
    $payload = presignedUpload($storage);

    expect($payload)
        ->toHaveKeys(['upload_url', 'object_key', 'content_type', 'headers', 'upload_token', 'expires_at'])
        ->and($payload['content_type'])->toBe('video/mp4')
        ->and($payload['headers'])->toBe(['Content-Type' => 'video/mp4']);

    $this->assertDatabaseHas('alumni_upload_sessions', [
        'object_key' => $payload['object_key'],
        'content_type' => 'video/mp4',
        'expected_size' => 1024,
        'status' => 'presigned',
    ]);
});

it('returns 422 when the video exceeds 100 MB', function () {
    fakeVideoStorage();

    $this->postJson(route('alumni.registration.presign'), [
        'file_name' => 'cerita.mp4',
        'content_type' => 'video/mp4',
        'size' => 104857601,
    ])->assertUnprocessable()->assertJsonValidationErrors(['size']);
});

it('returns 422 when the browser MIME does not match the extension', function () {
    fakeVideoStorage();

    $this->postJson(route('alumni.registration.presign'), [
        'file_name' => 'cerita.mp4',
        'content_type' => 'video/quicktime',
        'size' => 1024,
    ])->assertUnprocessable()->assertJsonValidationErrors(['file_name']);
});

it('returns 422 when the confirmation token is not authorized', function () {
    $storage = fakeVideoStorage();
    $upload = presignedUpload($storage);

    confirmUpload(registrationPayload([
        'upload_token' => Str::random(64),
        'object_key' => $upload['object_key'],
    ]))->assertUnprocessable()->assertJsonValidationErrors(['upload_token']);

    $this->assertDatabaseCount('alumni_peserta', 0);
});

it('returns 422 and creates no participant when the object was not uploaded', function () {
    $storage = fakeVideoStorage();
    $upload = presignedUpload($storage);
    $storage->shouldReceive('verify')->once()->andThrow(new RuntimeException('Video belum ditemukan di penyimpanan.'));
    $storage->shouldReceive('delete')->once()->with($upload['object_key']);

    confirmUpload(registrationPayload([
        'upload_token' => $upload['upload_token'],
        'object_key' => $upload['object_key'],
    ]))->assertUnprocessable()->assertJsonValidationErrors(['object_key']);

    $this->assertDatabaseCount('alumni_peserta', 0);
    $this->assertDatabaseHas('alumni_upload_sessions', ['object_key' => $upload['object_key'], 'status' => 'failed']);
});

it('returns 422 when the confirmation submits a different object key', function () {
    $storage = fakeVideoStorage();
    $upload = presignedUpload($storage);

    confirmUpload(registrationPayload([
        'upload_token' => $upload['upload_token'],
        'object_key' => 'alumni-bercerita/2027-2028/original/'.Str::uuid().'.mp4',
    ]))->assertUnprocessable()->assertJsonValidationErrors(['upload_token']);

    $this->assertDatabaseCount('alumni_peserta', 0);
});

it('registers only a verified upload and rejects replay', function () {
    $storage = fakeVideoStorage();
    $upload = presignedUpload($storage);
    $storage->shouldReceive('verify')->once()->andReturn([
        'disk' => 'alumni_private',
        'object_key' => $upload['object_key'],
        'original_name' => 'cerita.mp4',
        'mime_type' => 'video/mp4',
        'ukuran' => 1024,
    ]);

    $request = registrationPayload(['upload_token' => $upload['upload_token'], 'object_key' => $upload['object_key']]);
    confirmUpload($request)->assertCreated()->assertJsonPath('registration_code', fn ($code) => str_starts_with($code, 'AB-'));

    $this->assertDatabaseHas('alumni_video', ['object_key' => $upload['object_key'], 'disk' => 'alumni_private', 'ukuran' => 1024]);
    $this->assertDatabaseHas('alumni_upload_sessions', ['object_key' => $upload['object_key'], 'status' => 'completed']);

    confirmUpload($request)->assertUnprocessable()->assertJsonValidationErrors(['upload_token']);
    $this->assertDatabaseCount('alumni_peserta', 1);
});
