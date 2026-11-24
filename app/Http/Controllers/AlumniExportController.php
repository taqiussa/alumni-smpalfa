<?php

namespace App\Http\Controllers;

use App\Models\AlumniPeserta;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AlumniExportController extends Controller
{
    public function __invoke(Request $request): StreamedResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'angkatan' => ['nullable', 'integer'],
            'kurasi' => ['nullable', 'in:menunggu,lolos,revisi,ditolak'],
            'publikasi' => ['nullable', 'in:belum,sudah'],
            'penilaian' => ['nullable', 'in:belum,sudah'],
            'tanggal' => ['nullable', 'date'],
        ]);

        return response()->streamDownload(function () use ($filters): void {
            $output = fopen('php://output', 'wb');
            fputcsv($output, ['Kode', 'Nama', 'Tahun Kelulusan', 'WhatsApp', 'Kurasi', 'Publikasi', 'Penilaian', 'Tanggal Daftar']);
            AlumniPeserta::query()->when($filters['search'] ?? null, fn ($query, $search) => $query->where(fn ($nested) => $nested->where('nama_lengkap', 'like', '%'.$search.'%')->orWhere('kode_registrasi', 'like', '%'.$search.'%')->orWhereHas('activeVideo', fn ($video) => $video->where('original_name', 'like', '%'.$search.'%'))))->when($filters['angkatan'] ?? null, fn ($query, $year) => $query->where('tahun_kelulusan', $year))->when($filters['kurasi'] ?? null, fn ($query, $status) => $query->where('status_kurasi', $status))->when($filters['publikasi'] ?? null, fn ($query, $status) => $query->where('status_publikasi', $status))->when($filters['penilaian'] ?? null, fn ($query, $status) => $query->where('status_penilaian', $status))->when($filters['tanggal'] ?? null, fn ($query, $date) => $query->whereDate('registered_at', $date))->orderBy('id')->lazyById()->each(function (AlumniPeserta $peserta) use ($output): void {
                fputcsv($output, array_map(fn (string|int|null $value): string|int|null => is_string($value) && preg_match('/^[=+\-@]/', $value) ? "'".$value : $value, [$peserta->kode_registrasi, $peserta->nama_lengkap, $peserta->tahun_kelulusan, $peserta->whatsapp, $peserta->status_kurasi, $peserta->status_publikasi, $peserta->status_penilaian, $peserta->registered_at?->timezone('Asia/Jakarta')->format('Y-m-d H:i')]));
            });
            fclose($output);
        }, 'alumni-peserta.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
