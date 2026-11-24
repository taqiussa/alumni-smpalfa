<?php

use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Component;
new #[Layout('layouts.public'), Title('Alumni Bercerita')] class extends Component { public function mount(): void {} };
?>

<main class="min-h-screen bg-amber-50 text-slate-900"><section class="mx-auto max-w-5xl px-5 py-16 sm:py-24"><p class="font-semibold text-amber-700">SMP Al Musyaffa · PSB TA 2027/2028</p><h1 class="mt-3 text-4xl font-bold tracking-tight sm:text-6xl">Alumni Bercerita</h1><p class="mt-6 max-w-2xl text-lg text-slate-600">Bagikan kisahmu dan bantu calon siswa mengenal pengalaman belajar di SMP Al Musyaffa.</p><div class="mt-8"><flux:button href="{{ route('alumni.register') }}" variant="primary">Daftar karya</flux:button></div></section><section class="mx-auto grid max-w-5xl gap-5 px-5 pb-16 md:grid-cols-3"><article class="rounded-2xl bg-white p-6 shadow-sm"><h2 class="font-semibold">Video</h2><p class="mt-2 text-sm text-slate-600">MP4/MOV, portrait 9:16, maksimum 90 detik dan 100 MB.</p></article><article class="rounded-2xl bg-white p-6 shadow-sm"><h2 class="font-semibold">Kurasi</h2><p class="mt-2 text-sm text-slate-600">Tim media meninjau kelayakan publikasi.</p></article><article class="rounded-2xl bg-white p-6 shadow-sm"><h2 class="font-semibold">Penghargaan</h2><p class="mt-2 text-sm text-slate-600">Tiga video terbaik menerima penghargaan.</p></article></section></main>
