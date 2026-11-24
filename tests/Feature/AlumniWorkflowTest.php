<?php

use App\Models\AlumniKriteria;
use App\Models\AlumniPeserta;
use App\Models\AlumniVideo;
use App\Models\User;
use App\Services\AlumniVideoStorage;
use App\Services\AlumniWorkflowService;

function alumniParticipant(array $attributes = []): AlumniPeserta
{
    return AlumniPeserta::factory()->create($attributes);
}

test('curation stores its audit trail and revision request atomically', function (): void {
    $actor = User::factory()->create();
    $peserta = alumniParticipant(['status_kurasi' => 'menunggu']);

    app(AlumniWorkflowService::class)->curate($peserta, 'revisi', 'Mohon perjelas audio.', $actor->id);

    expect($peserta->refresh()->status_kurasi)->toBe('revisi');
    $this->assertDatabaseHas('alumni_kurasi', ['peserta_id' => $peserta->id, 'status_sebelum' => 'menunggu', 'status_sesudah' => 'revisi']);
    $this->assertDatabaseHas('alumni_revisi', ['peserta_id' => $peserta->id, 'catatan' => 'Mohon perjelas audio.']);
    $this->assertDatabaseHas('alumni_audit_log', ['event' => 'alumni.curated', 'auditable_id' => $peserta->id]);
});

test('only a curated participant can be published and scored with existing criteria', function (): void {
    $actor = User::factory()->create();
    $peserta = alumniParticipant(['status_kurasi' => 'lolos']);
    $criterion = AlumniKriteria::query()->create(['periode' => $peserta->periode, 'nama' => 'Kejelasan cerita', 'bobot' => 100, 'urutan' => 1, 'aktif' => true]);

    $workflow = app(AlumniWorkflowService::class);
    $workflow->publish($peserta, $actor->id, 'Instagram', 'https://instagram.example.test/p/1');
    $workflow->score($peserta, $actor->id, [$criterion->id => 87.5]);

    expect($peserta->refresh()->status_publikasi)->toBe('sudah')->and($peserta->status_penilaian)->toBe('sudah');
    $this->assertDatabaseHas('alumni_publikasi', ['peserta_id' => $peserta->id, 'status' => 'dipublikasikan']);
    $this->assertDatabaseHas('alumni_penilaian', ['peserta_id' => $peserta->id, 'kriteria_id' => $criterion->id, 'juri_id' => $actor->id, 'nilai' => 87.5]);
});

test('authenticated video download redirects the browser to a presigned S3 URL', function (): void {
    $user = User::factory()->create();
    $peserta = alumniParticipant();
    $video = AlumniVideo::query()->create(['peserta_id' => $peserta->id, 'versi' => 1, 'disk' => 'alumni_private', 'object_key' => 'alumni-bercerita/2027/original/video.mp4', 'original_name' => 'cerita.mp4', 'mime_type' => 'video/mp4', 'ukuran' => 1234, 'aktif' => true]);
    $storage = Mockery::mock(AlumniVideoStorage::class);
    $storage->shouldReceive('downloadUrl')->once()->withArgs(fn (AlumniVideo $actual, string $filename): bool => $actual->is($video) && str_ends_with($filename, '.mp4'))->andReturn('https://s3.example.test/presigned-download');
    app()->instance(AlumniVideoStorage::class, $storage);

    $this->actingAs($user)->get(route('alumni.video.download', $video))->assertRedirect('https://s3.example.test/presigned-download');
});
