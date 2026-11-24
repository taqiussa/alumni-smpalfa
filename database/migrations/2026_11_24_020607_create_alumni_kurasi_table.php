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
        Schema::create('alumni_kurasi', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('peserta_id')->constrained('alumni_peserta')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('status_sebelum', 24)->nullable();
            $table->string('status_sesudah', 24);
            $table->text('catatan')->nullable();
            $table->timestamp('diproses_at')->useCurrent();
            $table->timestamps();
            $table->index(['peserta_id', 'diproses_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumni_kurasi');
    }
};
