<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RejectedUser extends Model
{
    protected $table = 'rejected_users';
    protected $fillable = [
        'email',
        'phone',
        'reason',
        'rejected_at'
    ];
}
