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
        Schema::create('alumni_kriteria', function (Blueprint $table): void {
            $table->id();
            $table->string('periode', 20)->index();
            $table->string('nama', 120);
            $table->decimal('bobot', 5, 2);
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            $table->unique(['periode', 'nama']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumni_kriteria');
    }
};
