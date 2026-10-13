<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceEmployeeSync extends Model
{
    use HasFactory;

    protected $fillable = ['source_employee_id', 'payload', 'payload_hash', 'source_version', 'delivered_version', 'attempts', 'next_retry_at', 'lease_token', 'lease_until', 'last_error', 'last_synced_at'];

    protected function casts(): array
    {
        return ['payload' => 'array', 'source_version' => 'integer', 'delivered_version' => 'integer', 'attempts' => 'integer', 'next_retry_at' => 'datetime', 'lease_until' => 'datetime', 'last_synced_at' => 'datetime'];
    }
}
