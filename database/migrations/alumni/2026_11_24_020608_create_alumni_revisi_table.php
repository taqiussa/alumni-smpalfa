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
        Schema::create('alumni_revisi', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('peserta_id')->constrained('alumni_peserta')->cascadeOnDelete();
            $table->unsignedBigInteger('diminta_oleh')->nullable();
            $table->text('catatan');
            $table->timestamp('batas_revisi_at')->nullable();
            $table->timestamp('dipenuhi_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumni_revisi');
    }
};
