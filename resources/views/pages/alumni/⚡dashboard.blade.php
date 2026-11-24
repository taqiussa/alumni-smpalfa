<?php

use App\Models\AlumniPeserta;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app'), Title('Dashboard Alumni')] class extends Component
{
    public array $stats = [];

    public function mount(): void
    {
        $this->stats = [
            'Peserta' => AlumniPeserta::query()->count(),
            'Menunggu' => AlumniPeserta::query()->where('status_kurasi', 'menunggu')->count(),
            'Lolos' => AlumniPeserta::query()->where('status_kurasi', 'lolos')->count(),
            'Dipublikasikan' => AlumniPeserta::query()->where('status_publikasi', 'sudah')->count(),
        ];
    }
};
?>

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @foreach ($stats as $label => $value)
        <flux:card wire:key="{{ $label }}">
            <p class="text-sm text-zinc-500">{{ $label }}</p>
            <p class="mt-2 text-3xl font-bold">{{ $value }}</p>
        </flux:card>
    @endforeach
</div>
