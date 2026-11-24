@props(['video'])

@if ($video)
    <div x-data="{ loading: true, failed: false, close() { const video = this.$refs.video; video.pause(); video.removeAttribute('src'); video.load(); } }" x-on:alumni-video-close.window="close()" class="relative overflow-hidden rounded-xl bg-black">
        <div x-show="loading" class="absolute inset-0 grid place-items-center text-sm text-white">Memuat video…</div>
        <video x-ref="video" controls preload="metadata" playsinline class="relative aspect-video w-full" x-on:loadeddata="loading = false" x-on:error="loading = false; failed = true">
            <source src="{{ route('alumni.video.play', $video) }}" type="{{ $video->mime_type }}">
            Browser Anda tidak mendukung pemutar video.
        </video>
        <p x-show="failed" x-cloak class="p-3 text-sm text-red-200">Video tidak tersedia atau tidak dapat dimuat.</p>
    </div>
@else
    <div class="grid aspect-video place-items-center rounded-xl border border-dashed border-slate-500 bg-slate-50 text-sm text-slate-600">Video belum tersedia.</div>
@endif
