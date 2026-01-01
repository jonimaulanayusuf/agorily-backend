<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    const PAYMENT_PENDING = 'pending';
    const PAYMENT_UNPAID = 'unpaid';
    const PAYMENT_PAID = 'paid';

    protected $fillable = [
        'invoice',
        'payment_code',
        'payment_method',
        'payment_status',
        'total',
        'user_id',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
