<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'user_id',
        'cashier_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'delivery_address',
        'province_city',
        'customer_note',
        'delivery_method',
        'subtotal',
        'discount_amount',
        'delivery_fee',
        'total_amount',
        'payment_method',
        'payment_status',
        'khqr_string',
        'khqr_md5',
        'khqr_expiration',
        'payment_proof_image',
        'bakong_hash',
        'paid_at',
        'order_status',
        'source',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'khqr_expiration' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function getCanBeCancelledAttribute(): bool
    {
        return in_array($this->order_status, ['pending', 'confirmed']);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->order_status) {
            'pending' => 'bg-amber-100 text-amber-800 border-amber-300',
            'confirmed' => 'bg-blue-100 text-blue-800 border-blue-300',
            'processing' => 'bg-indigo-100 text-indigo-800 border-indigo-300',
            'ready_pickup' => 'bg-purple-100 text-purple-800 border-purple-300',
            'shipped' => 'bg-cyan-100 text-cyan-800 border-cyan-300',
            'delivered', 'completed' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'cancelled', 'rejected' => 'bg-rose-100 text-rose-800 border-rose-300',
            'refunded' => 'bg-slate-100 text-slate-800 border-slate-300',
            default => 'bg-gray-100 text-gray-800 border-gray-300',
        };
    }

    public function getPaymentBadgeAttribute(): string
    {
        return match ($this->payment_status) {
            'paid' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'pending' => 'bg-amber-100 text-amber-800 border-amber-300',
            'failed' => 'bg-rose-100 text-rose-800 border-rose-300',
            'cancelled' => 'bg-gray-100 text-gray-800 border-gray-300',
            'refunded' => 'bg-purple-100 text-purple-800 border-purple-300',
            default => 'bg-gray-100 text-gray-800 border-gray-300',
        };
    }
}
