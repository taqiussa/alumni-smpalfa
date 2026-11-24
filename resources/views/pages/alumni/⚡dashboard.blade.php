<?php

use App\Models\AlumniPeserta; use Livewire\Component;
new class extends Component { public array $stats=[]; public function mount(): void { $base=AlumniPeserta::query(); $this->stats=['Peserta'=>$base->count(),'Menunggu'=>AlumniPeserta::where('status_kurasi','menunggu')->count(),'Lolos'=>AlumniPeserta::where('status_kurasi','lolos')->count(),'Dipublikasikan'=>AlumniPeserta::where('status_publikasi','sudah')->count()]; } };
?>

<x-layouts::app title="Dashboard Alumni"><div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">@foreach($stats as $label=>$value)<flux:card wire:key="{{ $label }}"><p class="text-sm text-zinc-500">{{ $label }}</p><p class="mt-2 text-3xl font-bold">{{ $value }}</p></flux:card>@endforeach</div></x-layouts::app>
