<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactAttachment extends Model
{
    protected $table = 'contact_attachments';
    protected $fillable = [
        'contact_request_id',
        'file_path',
    ];

    public function contact()
    {
        return $this->belongsTo(ContactRequest::class);
    }
}
