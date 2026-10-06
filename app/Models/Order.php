<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    public const STOCK_RESERVED_STATUSES = ['pending', 'processing', 'shipped'];

    protected $fillable = [
        'user_id',
        'order_number',
        'total_amount',
        'status',
        'delivered_at',
        'payment_status',
        'payment_method',
        'shipping_name',
        'shipping_phone',
        'shipping_address',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function customerPayments(): HasMany
    {
        return $this->hasMany(CustomerPayment::class);
    }

    public function getReceivedAmountCentsAttribute(): int
    {
        if ($this->status === 'delivered') {
            return (int) round((float) $this->total_amount * 100);
        }

        $payments = $this->customerPayments;

        if ($payments->isEmpty() && $this->payment_status === 'paid') {
            return (int) round((float) $this->total_amount * 100);
        }

        return $payments->sum(
            fn (CustomerPayment $payment): int => (int) round((float) $payment->amount * 100),
        );
    }

    public function getEffectivePaymentStatusAttribute(): string
    {
        return $this->status === 'delivered' ? 'paid' : $this->payment_status;
    }

    public function getBalanceDueCentsAttribute(): int
    {
        return max(0, (int) round((float) $this->total_amount * 100) - $this->received_amount_cents);
    }

    public function getGrossProfitCentsAttribute(): ?int
    {
        $items = $this->items;

        if ($items->isEmpty() || $items->contains(fn (OrderItem $item): bool => $item->unit_cost === null)) {
            return null;
        }

        return $items->sum(
            fn (OrderItem $item): int => (
                (int) round((float) $item->unit_price * 100)
                - (int) round((float) $item->unit_cost * 100)
            ) * $item->quantity,
        );
    }
}
