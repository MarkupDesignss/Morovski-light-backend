<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dispute extends Model
{
    protected $fillable = ['order_id', 'opened_by', 'reason', 'description', 'status', 'resolution', 'resolution_notes', 'resolved_by', 'resolved_at'];

    protected $casts = ['resolved_at' => 'datetime'];

    public function order() { return $this->belongsTo(Order::class,'order_id'); }
    public function user() { return $this->belongsTo(User::class, 'opened_by'); }
    // public function opener() { return $this->belongsTo(User::class, 'opened_by'); }
    public function messages() { return $this->hasMany(DisputeMessage::class); }
    
    public function scopeOpen($query) { return $query->where('status', 'open'); }
    public function evidences()
    {
        return $this->hasMany(DisputeEvidence::class);
    }
}