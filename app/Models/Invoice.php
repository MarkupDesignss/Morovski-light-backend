<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = 'invoices';
    protected $guarded = [];
    
    protected $casts = [
        'valid_until' => 'datetime',
    ];


    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
    
    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }
    
    public function salesExecutive()
    {
        return $this->belongsTo(User::class, 'sales_executive_id', 'id');
    }
    
    public function orders()
    {
        return $this->belongsTo(Order::class, 'order_number', 'order_number');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function payments()
    {
        return $this->hasMany(PiPayment::class);
    }
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
