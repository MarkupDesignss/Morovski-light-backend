<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PiPayment extends Model
{
    protected $table = 'pi_payments';
    protected $guarded = [];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
