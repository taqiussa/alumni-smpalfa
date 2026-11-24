<?php

namespace App\Http\Controllers;

use App\Models\AlumniPeserta;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AlumniExportController extends Controller
{
    public function __invoke(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $output = fopen('php://output', 'wb');
            fputcsv($output, ['Kode', 'Nama', 'Tahun Kelulusan', 'WhatsApp', 'Kurasi', 'Publikasi']);
            AlumniPeserta::query()->lazyById()->each(function (AlumniPeserta $peserta) use ($output): void {
                fputcsv($output, array_map(fn (string|int|null $value): string|int|null => is_string($value) && preg_match('/^[=+\-@]/', $value) ? "'".$value : $value, [$peserta->kode_registrasi, $peserta->nama_lengkap, $peserta->tahun_kelulusan, $peserta->whatsapp, $peserta->status_kurasi, $peserta->status_publikasi]));
            });
            fclose($output);
        }, 'alumni-peserta.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
