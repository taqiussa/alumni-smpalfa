@props(['status'])

@php
    $labels = ['menunggu' => 'Menunggu', 'lolos' => 'Lolos', 'ditolak' => 'Ditolak', 'revisi' => 'Revisi', 'belum' => 'Belum', 'sudah' => 'Sudah', 'dipublikasikan' => 'Dipublikasikan', 'dibatalkan' => 'Dibatalkan'];
    $colors = ['lolos' => 'bg-brand-accent-light text-brand-accent-dark', 'sudah' => 'bg-primary-light/25 text-primary-dark', 'dipublikasikan' => 'bg-primary-light/25 text-primary-dark', 'ditolak' => 'bg-red-100 text-red-800', 'revisi' => 'bg-secondary-light text-slate-800', 'menunggu' => 'bg-slate-100 text-slate-700', 'belum' => 'bg-slate-100 text-slate-700'];
@endphp

<span {{ $attributes->class(['inline-flex whitespace-nowrap rounded-full px-2 py-0.5 text-xs font-medium', $colors[$status] ?? 'bg-slate-100 text-slate-700']) }}>{{ $labels[$status] ?? ucfirst($status) }}</span>
