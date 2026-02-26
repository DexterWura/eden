<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactSubmission extends Model
{
    protected $table = 'contact_submissions';

    protected $fillable = ['name', 'email', 'subject', 'message', 'reply_subject', 'reply_body', 'replied_at'];

    protected $casts = [
        'replied_at' => 'datetime',
    ];
}
