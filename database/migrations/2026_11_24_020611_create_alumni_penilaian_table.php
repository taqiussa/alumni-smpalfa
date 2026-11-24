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
        Schema::create('alumni_penilaian', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('peserta_id')->constrained('alumni_peserta')->cascadeOnDelete();
            $table->foreignId('kriteria_id')->constrained('alumni_kriteria')->cascadeOnDelete();
            $table->unsignedBigInteger('juri_id');
            $table->decimal('nilai', 5, 2);
            $table->boolean('final')->default(false);
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
            $table->unique(['peserta_id', 'kriteria_id', 'juri_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumni_penilaian');
    }
};
