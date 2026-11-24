<?php

use App\Models\AlumniPeserta;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app'), Title('Dashboard Alumni')] class extends Component
{
    public function mount(): void {}

    #[Computed]
    public function stats(): array
    {
        $counts = AlumniPeserta::query()->selectRaw("count(*) total, coalesce(sum(status_kurasi = 'menunggu'), 0) menunggu, coalesce(sum(status_kurasi = 'lolos'), 0) lolos, coalesce(sum(status_kurasi = 'revisi'), 0) revisi, coalesce(sum(status_kurasi = 'ditolak'), 0) ditolak, coalesce(sum(status_publikasi = 'sudah'), 0) publikasi, coalesce(sum(status_kurasi = 'lolos' and status_publikasi = 'belum'), 0) siap_publikasi, coalesce(sum(status_penilaian = 'sudah'), 0) dinilai, coalesce(sum(status_kurasi = 'lolos' and status_penilaian = 'belum'), 0) siap_nilai")->first();
        return [
            ['label' => 'Peserta terdaftar', 'value' => $counts->total, 'route' => route('alumni.participants')], ['label' => 'Karya video', 'value' => AlumniPeserta::query()->whereHas('activeVideo')->count(), 'route' => route('alumni.participants')],
            ['label' => 'Menunggu kurasi', 'value' => $counts->menunggu, 'route' => route('alumni.curation', ['kurasi' => 'menunggu'])], ['label' => 'Lolos kurasi', 'value' => $counts->lolos, 'route' => route('alumni.curation', ['kurasi' => 'lolos'])],
            ['label' => 'Butuh revisi', 'value' => $counts->revisi, 'route' => route('alumni.curation', ['kurasi' => 'revisi'])], ['label' => 'Ditolak', 'value' => $counts->ditolak, 'route' => route('alumni.curation', ['kurasi' => 'ditolak'])],
            ['label' => 'Dipublikasikan', 'value' => $counts->publikasi, 'route' => route('alumni.publications', ['publikasi' => 'sudah'])], ['label' => 'Siap publikasi', 'value' => $counts->siap_publikasi, 'route' => route('alumni.publications', ['publikasi' => 'belum'])],
            ['label' => 'Sudah dinilai', 'value' => $counts->dinilai, 'route' => route('alumni.scoring', ['penilaian' => 'sudah'])], ['label' => 'Siap dinilai', 'value' => $counts->siap_nilai, 'route' => route('alumni.scoring', ['penilaian' => 'belum'])],
        ];
    }
    #[Computed] public function recentParticipants() { return AlumniPeserta::query()->with('activeVideo')->latest('registered_at')->limit(10)->get(); }
    #[Computed] public function graduationChart() { return AlumniPeserta::query()->select('tahun_kelulusan', DB::raw('count(*) as total'))->groupBy('tahun_kelulusan')->orderBy('tahun_kelulusan')->get(); }
    #[Computed] public function registrationTrend() { return AlumniPeserta::query()->selectRaw('date(registered_at) label, count(*) total')->where('registered_at', '>=', now()->subDays(13)->startOfDay())->groupBy('label')->orderBy('label')->get(); }
};
?>

<div class="space-y-6">
    <div><flux:heading size="xl">Dashboard Alumni</flux:heading><flux:subheading>Monitoring pendaftaran, kurasi, publikasi, dan penilaian.</flux:subheading></div>
    <div class="grid grid-cols-2 gap-3 xl:grid-cols-5">@foreach ($this->stats as $stat)<a wire:navigate href="{{ $stat['route'] }}" class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm transition hover:border-primary-light hover:shadow-md" wire:key="{{ $stat['label'] }}"><p class="text-xs text-slate-600">{{ $stat['label'] }}</p><p class="mt-1 text-2xl font-semibold text-slate-800">{{ $stat['value'] }}</p></a>@endforeach</div>
    <div class="grid gap-4 xl:grid-cols-2">
        <flux:card><flux:heading size="lg">Peserta per tahun kelulusan</flux:heading><div class="mt-5 flex min-h-44 items-end gap-2">@forelse($this->graduationChart as $point)<div class="flex flex-1 flex-col items-center gap-1"><span class="text-xs">{{ $point->total }}</span><div class="w-full rounded-t bg-primary" style="height: {{ max(8, $point->total / max(1, $this->graduationChart->max('total')) * 120) }}px"></div><span class="text-[10px] text-slate-600">{{ $point->tahun_kelulusan }}</span></div>@empty<p class="m-auto text-sm text-slate-600">Belum ada data peserta.</p>@endforelse</div></flux:card>
        <flux:card><flux:heading size="lg">Tren pendaftaran 14 hari</flux:heading><div class="mt-5 flex min-h-44 items-end gap-2">@forelse($this->registrationTrend as $point)<div class="flex flex-1 flex-col items-center gap-1"><span class="text-xs">{{ $point->total }}</span><div class="w-full rounded-t bg-brand-accent" style="height: {{ max(8, $point->total / max(1, $this->registrationTrend->max('total')) * 120) }}px"></div><span class="text-[10px] text-slate-600">{{ \Illuminate\Support\Carbon::parse($point->label)->format('d M') }}</span></div>@empty<p class="m-auto text-sm text-slate-600">Belum ada pendaftaran dalam 14 hari terakhir.</p>@endforelse</div></flux:card>
    </div>
    <div class="grid gap-4 xl:grid-cols-3">@foreach(['Progres kurasi' => [2,3,4,5], 'Progres penilaian' => [8,9], 'Pekerjaan tertunda' => [2,4,7,9]] as $heading => $keys)<flux:card><flux:heading size="lg">{{ $heading }}</flux:heading><div class="mt-4 space-y-2">@foreach(collect($this->stats)->only($keys) as $stat)<a wire:navigate href="{{ $stat['route'] }}" class="flex justify-between text-sm"><span>{{ $stat['label'] }}</span><strong>{{ $stat['value'] }}</strong></a>@endforeach</div></flux:card>@endforeach</div>
    <flux:card><div class="flex items-center justify-between"><flux:heading size="lg">Peserta terbaru</flux:heading><flux:button size="sm" :href="route('alumni.participants')" wire:navigate>Lihat semua</flux:button></div><div class="mt-4 overflow-x-auto"><table class="w-full text-left text-sm"><thead class="text-xs text-slate-600"><tr><th class="px-2 py-2">Peserta</th><th class="px-2 py-2">Angkatan</th><th class="px-2 py-2">Kurasi</th><th class="px-2 py-2">Publikasi</th><th class="px-2 py-2">Nilai</th><th class="px-2 py-2">Daftar</th><th></th></tr></thead><tbody>@forelse($this->recentParticipants as $peserta)<tr class="border-t border-slate-200"><td class="px-2 py-2"><p class="font-medium text-slate-800">{{ $peserta->nama_lengkap }}</p><p class="text-xs text-slate-600">{{ $peserta->activeVideo?->original_name ?? 'Belum ada video' }}</p></td><td class="px-2 py-2">{{ $peserta->tahun_kelulusan }}</td><td class="px-2 py-2"><x-alumni.status-badge :status="$peserta->status_kurasi"/></td><td class="px-2 py-2"><x-alumni.status-badge :status="$peserta->status_publikasi"/></td><td class="px-2 py-2"><x-alumni.status-badge :status="$peserta->status_penilaian"/></td><td class="px-2 py-2">{{ $peserta->registered_at->timezone('Asia/Jakarta')->format('d M Y') }}</td><td class="px-2 py-2"><a wire:navigate href="{{ route('alumni.participants.show',$peserta) }}" class="font-medium text-primary-dark hover:text-primary">Detail</a></td></tr>@empty<tr><td colspan="7" class="px-2 py-8 text-center text-slate-600">Belum ada peserta.</td></tr>@endforelse</tbody></table></div></flux:card>
</div>
