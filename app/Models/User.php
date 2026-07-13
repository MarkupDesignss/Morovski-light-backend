<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'full_name',
        'email',
        'role_id',
        'phone',
        'country',
        'business_status',
        'profile_picture',
        'terms_condition',
        'business_rejected_at',
        'account_type',
        'warehouse_id',
        'password',
        'otp',
        'otp_expires_at',
        'email_verified_at',
        'provider',
        'provider_id',
        'is_registered',
        'delete_otp',
        'delete_otp_expires_at',
        'delete_reason',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'otp_expires_at' => 'datetime',
        'password' => 'hashed',
        'business_rejected_at' => 'datetime',
        'is_registered' => 'boolean',
    ];

    /**
     * JWT Identifier
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * JWT Custom Claims
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Business profile relation
     */
    public function businessProfile()
    {
        return $this->hasOne(BusinessProfile::class);
    }


    /**
     * User items (IMPORTANT for auction owner)
     */
    public function items()
    {
        return $this->hasMany(Item::class);
    }
    public function cartItems()
    {
        return $this->hasMany(Cart::class);
    }
    
    public function backInStockNotifications()
    {
        return $this->hasMany(BackInStockNotification::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_users');
    }

    /**
     * Get unread notifications count (for badge )
     */
    public function notificationSetting()
    {
        return $this->hasOne(NotificationSetting::class);
    }
    public function unreadNotificationCount()
    {
        return $this->unreadNotifications()->count();
    }
    public function getProfilePictureAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }
    public function shippingAddresses()
    {
        return $this->hasMany(ShippingAddress::class);
    }
    public function addresses()
    {
        return $this->hasMany(ShippingAddress::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
}
