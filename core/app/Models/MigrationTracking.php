<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MigrationTracking extends Model
{
    protected $table = 'migration_tracking';
    
    protected $guarded = ['id'];

    protected $casts = [
        'file_modified_at' => 'datetime',
        'last_run_at' => 'datetime',
    ];

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRan($query)
    {
        return $query->where('status', 'ran');
    }

    public function scopeModified($query)
    {
        return $query->where('status', 'modified');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function isModified()
    {
        return $this->status === 'modified';
    }

    public function needsRerun()
    {
        return in_array($this->status, ['pending', 'modified']);
    }
}

