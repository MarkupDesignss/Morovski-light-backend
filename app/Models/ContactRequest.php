<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactRequest extends Model
{
    protected $table = 'contact_requests';
   protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'category_id',
        'subject',
        'message',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function attachments()
    {
        return $this->hasMany(ContactAttachment::class);
    }
}
