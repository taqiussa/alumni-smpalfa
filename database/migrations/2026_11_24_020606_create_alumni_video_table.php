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
        Schema::create('alumni_video', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('peserta_id')->constrained('alumni_peserta')->cascadeOnDelete();
            $table->unsignedSmallInteger('versi')->default(1);
            $table->string('disk', 32)->default('alumni_private');
            $table->string('object_key', 512)->unique();
            $table->string('original_name', 255);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('ukuran');
            $table->unsignedInteger('durasi_detik')->nullable();
            $table->unsignedSmallInteger('lebar')->nullable();
            $table->unsignedSmallInteger('tinggi')->nullable();
            $table->string('orientasi', 16)->nullable();
            $table->boolean('aktif')->default(true)->index();
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamps();
            $table->unique(['peserta_id', 'versi']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumni_video');
    }
};
