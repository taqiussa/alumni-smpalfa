<?php

namespace App\Models;

use Database\Factories\AlumniAuditLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlumniAuditLog extends Model
{
    /** @use HasFactory<AlumniAuditLogFactory> */
    use HasFactory;

    protected $table = 'alumni_audit_log';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['before' => 'array', 'after' => 'array'];
    }
}
