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
        Schema::create('alumni_publikasi', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('peserta_id')->unique()->constrained('alumni_peserta')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('status', 24)->default('antrean')->index();
            $table->string('platform', 20)->nullable();
            $table->string('url_posting', 2048)->nullable();
            $table->string('published_object_key', 512)->nullable();
            $table->timestamp('dijadwalkan_at')->nullable();
            $table->timestamp('dipublikasikan_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumni_publikasi');
    }
};
