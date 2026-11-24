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
        Schema::create('alumni_upload_sessions', function (Blueprint $table): void {
            $table->id();
            $table->string('session_hash', 64)->index();
            $table->string('token_hash', 64)->unique();
            $table->string('object_key', 512)->unique();
            $table->string('original_name', 255);
            $table->string('extension', 8);
            $table->string('content_type', 100);
            $table->unsignedBigInteger('expected_size');
            $table->string('status', 20)->default('presigned')->index();
            $table->timestamp('expires_at')->index();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumni_upload_sessions');
    }
};
