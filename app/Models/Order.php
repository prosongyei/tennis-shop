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
            'pending' => 'bg-amber-500/20 text-amber-400 border-amber-500/40',
            'confirmed' => 'bg-sky-500/20 text-sky-400 border-sky-500/40',
            'processing' => 'bg-indigo-500/20 text-indigo-400 border-indigo-500/40',
            'ready_pickup' => 'bg-purple-500/20 text-purple-400 border-purple-500/40',
            'shipped' => 'bg-cyan-500/20 text-cyan-400 border-cyan-500/40',
            'delivered', 'completed' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/40',
            'cancelled', 'rejected' => 'bg-rose-500/20 text-rose-400 border-rose-500/40',
            'refunded' => 'bg-slate-800 text-slate-300 border-slate-700',
            default => 'bg-slate-800 text-slate-400 border-slate-700',
        };
    }

    public function getPaymentBadgeAttribute(): string
    {
        return match ($this->payment_status) {
            'paid' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/40',
            'pending' => 'bg-amber-500/20 text-amber-400 border-amber-500/40',
            'failed' => 'bg-rose-500/20 text-rose-400 border-rose-500/40',
            'cancelled' => 'bg-slate-800 text-slate-400 border-slate-700',
            'refunded' => 'bg-purple-500/20 text-purple-400 border-purple-500/40',
            default => 'bg-slate-800 text-slate-400 border-slate-700',
        };
    }
}
