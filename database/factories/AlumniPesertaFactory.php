<?php

namespace Database\Factories;

use App\Models\AlumniPeserta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AlumniPeserta>
 */
class AlumniPesertaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'periode' => '2026/2027',
            'kode_registrasi' => fake()->unique()->bothify('ALM-########'),
            'nama_lengkap' => fake()->name(),
            'tahun_kelulusan' => fake()->numberBetween(1990, 2026),
            'whatsapp' => fake()->numerify('08##########'),
            'domisili' => fake()->city(),
            'status' => 'bekerja',
            'platform_sosial' => 'instagram',
            'status_kurasi' => 'menunggu',
            'status_publikasi' => 'belum',
            'status_penilaian' => 'belum',
            'revision_token' => fake()->unique()->sha256(),
            'registered_at' => now(),
        ];
    }
}
