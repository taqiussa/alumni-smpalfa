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
        Schema::create('alumni_peserta', function (Blueprint $table): void {
            $table->id();
            $table->string('periode', 20)->index();
            $table->string('kode_registrasi', 24)->unique();
            $table->string('nama_lengkap', 150);
            $table->unsignedSmallInteger('tahun_kelulusan');
            $table->string('whatsapp', 30);
            $table->string('domisili', 150);
            $table->string('status', 20);
            $table->string('platform_sosial', 20);
            $table->string('username_instagram')->nullable();
            $table->string('link_instagram', 2048)->nullable();
            $table->string('username_tiktok')->nullable();
            $table->string('link_tiktok', 2048)->nullable();
            $table->string('status_kurasi', 24)->default('menunggu')->index();
            $table->string('status_publikasi', 24)->default('belum')->index();
            $table->string('status_penilaian', 24)->default('belum')->index();
            $table->string('revision_token', 64)->unique();
            $table->timestamp('registered_at')->useCurrent();
            $table->timestamps();
            $table->index(['periode', 'status_kurasi']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumni_peserta');
    }
};
