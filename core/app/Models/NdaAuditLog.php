<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NdaAuditLog extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'device_info' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function ndaDocument()
    {
        return $this->belongsTo(NdaDocument::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeForNda($query, $ndaId)
    {
        return $query->where('nda_document_id', $ndaId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
