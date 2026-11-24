<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('alumni_pengaturan', function (Blueprint $table): void {
            $table->id();
            $table->string('periode', 20)->unique();
            $table->boolean('pendaftaran_dibuka')->default(true);
            $table->timestamp('pendaftaran_mulai_at')->nullable();
            $table->timestamp('pendaftaran_tutup_at');
            $table->timestamp('revisi_tutup_at')->nullable();
            $table->unsignedInteger('maks_ukuran_video_mb')->default(100);
            $table->unsignedInteger('maks_durasi_video_detik')->default(90);
            $table->json('platform_wajib')->nullable();
            $table->json('pengaturan_penilaian')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumni_pengaturan');
    }
};
