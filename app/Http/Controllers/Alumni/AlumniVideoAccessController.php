<?php

namespace App\Http\Controllers\Alumni;

use App\Http\Controllers\Controller;
use App\Models\AlumniVideo;
use App\Services\AlumniVideoStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class AlumniVideoAccessController extends Controller
{
    public function play(AlumniVideo $video, AlumniVideoStorage $storage): RedirectResponse
    {
        abort_unless($video->aktif, 404);

        return redirect()->away($storage->playbackUrl($video));
    }

    public function download(AlumniVideo $video, AlumniVideoStorage $storage): RedirectResponse
    {
        abort_unless($video->aktif, 404);

        $participant = $video->peserta()->firstOrFail();
        $extension = pathinfo($video->original_name, PATHINFO_EXTENSION) ?: Str::after($video->mime_type, 'video/');
        $filename = Str::of($participant->nama_lengkap.'_'.$participant->tahun_kelulusan.'_'.pathinfo($video->original_name, PATHINFO_FILENAME))
            ->ascii()
            ->replaceMatches('/[^A-Za-z0-9._-]+/', '-')
            ->trim('-')
            ->append('.'.$extension)
            ->toString();

        return redirect()->away($storage->downloadUrl($video, $filename));
    }
}
