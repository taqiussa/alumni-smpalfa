<?php

namespace App\Models;

use Database\Factories\AlumniUploadSessionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AlumniUploadSession extends Model
{
    /** @use HasFactory<AlumniUploadSessionFactory> */
    use HasFactory;

    protected $table = 'alumni_upload_sessions';

    protected $fillable = [
        'session_hash',
        'token_hash',
        'object_key',
        'original_name',
        'extension',
        'content_type',
        'expected_size',
        'status',
        'expires_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'expected_size' => 'integer',
            'expires_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function video(): HasOne
    {
        return $this->hasOne(AlumniVideo::class, 'object_key', 'object_key');
    }
}
