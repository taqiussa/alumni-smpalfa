@props(['status'])

@php
    $labels = ['menunggu' => 'Menunggu', 'lolos' => 'Lolos', 'ditolak' => 'Ditolak', 'revisi' => 'Revisi', 'belum' => 'Belum', 'sudah' => 'Sudah', 'dipublikasikan' => 'Dipublikasikan', 'dibatalkan' => 'Dibatalkan'];
    $colors = ['lolos' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200', 'sudah' => 'bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-200', 'dipublikasikan' => 'bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-200', 'ditolak' => 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-200', 'revisi' => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200', 'menunggu' => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200', 'belum' => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200'];
@endphp

<span {{ $attributes->class(['inline-flex whitespace-nowrap rounded-full px-2 py-0.5 text-xs font-medium', $colors[$status] ?? 'bg-zinc-100 text-zinc-700']) }}>{{ $labels[$status] ?? ucfirst($status) }}</span>
