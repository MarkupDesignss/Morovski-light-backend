<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $table = 'orders';
    protected $guarded = [];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'commission' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime'
    ];
    
    public function promocode()
    {
        return $this->belongsTo(Promocode::class);
    }
        
    public function orderDetail()
    {
        return $this->hasOne(OrderDetail::class);
    }
    public function packedOrder()
    {
        return $this->hasOne(PackedOrder::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    
    public function tickets()
    {
        return $this->hasMany(SupportTicket::class);
    }
    

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function address()
    {
        return $this->belongsTo(ShippingAddress::class, 'address_id');
    }
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public static function generateOrderNumber(): string
    {
        return 'ORD-' . strtoupper(uniqid());
    }

    public function markAsShipped(?string $trackingNumber = null): void
    {
        $this->status = 'shipped';
        $this->tracking_number = $trackingNumber;
        $this->shipped_at = now();
        $this->save();
    }

    public function markAsDelivered(): void
    {
        $this->status = 'completed';
        $this->delivered_at = now();
        $this->save();
    }
    public function shippingAddress()
    {
        return $this->belongsTo(ShippingAddress::class, 'shipping_id');
    }
    public function dispute()
    {
        return $this->hasOne(Dispute::class);
    }

    /**
     * Get the review for this order.
     */
    public function review()
    {
        return $this->hasOne(Review::class);
    }
    public function shippingCharge()
    {
        return $this->belongsTo(ShippingCharge::class);
    }

    /**
     * Check if order is reviewable.
     */
    public function isReviewable(): bool
    {
        if ($this->status !== 'completed') {
            return false;
        }

        $reviewDeadline = Carbon::parse($this->completed_at)->addDays(30);

        return now()->lessThanOrEqualTo($reviewDeadline);
    }
    public function shipping()
    {
        return $this->belongsTo(ShippingAddress::class, 'shipping_id');
    }
      public function pendingOrderItems()
    {
        return $this->hasMany(PendingOrderItem::class, 'order_id');
    }
    public function replacementRequests()
    {
        return $this->hasMany(ReplacementRequest::class, 'order_id');
    }
}