<?php

use App\Models\AlumniPeserta;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app'), Title('Data Peserta Alumni')] class extends Component
{
    use WithPagination;
    #[Url] public string $search = '';
    #[Url] public string $angkatan = '';
    #[Url(as: 'kurasi')] public string $statusKurasi = '';
    #[Url(as: 'publikasi')] public string $statusPublikasi = '';
    #[Url(as: 'penilaian')] public string $statusPenilaian = '';
    #[Url] public string $tanggal = '';
    public ?int $videoParticipantId = null;
    public function mount(): void {}
    public function updated(): void { $this->resetPage(); }
    public function resetFilters(): void { $this->reset('search', 'angkatan', 'statusKurasi', 'statusPublikasi', 'statusPenilaian', 'tanggal'); $this->resetPage(); }
    #[Computed] public function years() { return AlumniPeserta::query()->select('tahun_kelulusan')->distinct()->orderByDesc('tahun_kelulusan')->pluck('tahun_kelulusan'); }
    #[Computed] public function participants() { return AlumniPeserta::query()->with('activeVideo')->when($this->search, fn($q) => $q->where(fn($nested) => $nested->where('nama_lengkap','like','%'.$this->search.'%')->orWhere('kode_registrasi','like','%'.$this->search.'%')->orWhereHas('activeVideo', fn($video) => $video->where('original_name','like','%'.$this->search.'%'))))->when($this->angkatan, fn($q) => $q->where('tahun_kelulusan',$this->angkatan))->when($this->statusKurasi, fn($q) => $q->where('status_kurasi',$this->statusKurasi))->when($this->statusPublikasi, fn($q) => $q->where('status_publikasi',$this->statusPublikasi))->when($this->statusPenilaian, fn($q) => $q->where('status_penilaian',$this->statusPenilaian))->when($this->tanggal, fn($q) => $q->whereDate('registered_at',$this->tanggal))->latest('registered_at')->paginate(15); }
    #[Computed] public function videoParticipant(): ?AlumniPeserta { return $this->videoParticipantId ? AlumniPeserta::query()->with('activeVideo')->find($this->videoParticipantId) : null; }
};
?>

<div class="space-y-5">
    <div class="flex flex-wrap items-end justify-between gap-3"><div><flux:heading size="xl">Data Peserta</flux:heading><flux:subheading>Daftar karya alumni yang masuk.</flux:subheading></div><flux:button :href="route('alumni.export.participants', request()->query())" icon="arrow-down-tray">Ekspor CSV</flux:button></div>
    <flux:card><div class="grid gap-3 md:grid-cols-2 xl:grid-cols-6"><flux:input wire:model.live.debounce.400ms="search" placeholder="Cari nama, kode, atau file…" class="xl:col-span-2"/><flux:select wire:model.live="angkatan"><option value="">Semua angkatan</option>@foreach($this->years as $year)<option value="{{ $year }}">{{ $year }}</option>@endforeach</flux:select><flux:select wire:model.live="statusKurasi"><option value="">Semua kurasi</option>@foreach(['menunggu','lolos','revisi','ditolak'] as $status)<option value="{{ $status }}">{{ ucfirst($status) }}</option>@endforeach</flux:select><flux:select wire:model.live="statusPublikasi"><option value="">Semua publikasi</option><option value="belum">Belum</option><option value="sudah">Sudah</option></flux:select><flux:select wire:model.live="statusPenilaian"><option value="">Semua penilaian</option><option value="belum">Belum</option><option value="sudah">Sudah</option></flux:select></div><div class="mt-3 flex flex-wrap items-center gap-3"><flux:input wire:model.live="tanggal" type="date" class="max-w-52"/><flux:button wire:click="resetFilters" variant="ghost" size="sm">Reset filter</flux:button><span wire:loading class="text-sm text-zinc-500">Memuat…</span></div></flux:card>
    <flux:card><div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead class="text-xs text-zinc-500"><tr><th class="px-2 py-2">No.</th><th class="px-2 py-2">Kode / peserta</th><th class="px-2 py-2">Angkatan</th><th class="px-2 py-2">WhatsApp</th><th class="px-2 py-2">Karya video</th><th class="px-2 py-2">Kurasi</th><th class="px-2 py-2">Publikasi</th><th class="px-2 py-2">Nilai</th><th class="px-2 py-2">Daftar</th><th></th></tr></thead><tbody>@forelse($this->participants as $peserta)<tr wire:key="participant-{{ $peserta->id }}" class="border-t border-zinc-200 align-top dark:border-zinc-800"><td class="px-2 py-2">{{ $this->participants->firstItem() + $loop->index }}</td><td class="px-2 py-2"><p class="font-medium">{{ $peserta->nama_lengkap }}</p><p class="text-xs text-zinc-500">{{ $peserta->kode_registrasi }}</p></td><td class="px-2 py-2">{{ $peserta->tahun_kelulusan }}</td><td class="px-2 py-2">{{ $peserta->whatsapp }}</td><td class="px-2 py-2 max-w-48 truncate">{{ $peserta->activeVideo?->original_name ?? '—' }}</td><td class="px-2 py-2"><x-alumni.status-badge :status="$peserta->status_kurasi"/></td><td class="px-2 py-2"><x-alumni.status-badge :status="$peserta->status_publikasi"/></td><td class="px-2 py-2"><x-alumni.status-badge :status="$peserta->status_penilaian"/></td><td class="px-2 py-2 whitespace-nowrap">{{ $peserta->registered_at->timezone('Asia/Jakarta')->format('d M Y') }}</td><td class="px-2 py-2 whitespace-nowrap"><a wire:navigate href="{{ route('alumni.participants.show',$peserta) }}" class="text-amber-700">Detail</a>@if($peserta->activeVideo)<button wire:click="set('videoParticipantId', {{ $peserta->id }})" class="ml-2 text-amber-700">Video</button><a href="{{ route('alumni.video.download',$peserta->activeVideo) }}" class="ml-2 text-amber-700">Unduh</a>@endif</td></tr>@empty<tr><td colspan="10" class="px-2 py-12 text-center text-zinc-500">Tidak ada peserta yang cocok.</td></tr>@endforelse</tbody></table></div><div class="mt-4">{{ $this->participants->links() }}</div></flux:card>
    @teleport('body')<div x-data="{ open: $wire.entangle('videoParticipantId') }" x-show="open" x-cloak x-on:keydown.escape.window="open = null; $dispatch('alumni-video-close')" class="fixed inset-0 z-50 grid place-items-center bg-black/70 p-4"><div class="w-full max-w-5xl rounded-2xl bg-zinc-950 p-4"><div class="mb-3 flex justify-end"><button x-on:click="open = null; $dispatch('alumni-video-close')" class="text-sm text-white">Tutup</button></div><x-alumni.video-player :video="$this->videoParticipant?->activeVideo"/></div></div>@endteleport
</div>
