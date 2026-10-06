<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    protected $fillable = [
        'supplier_id',
        'invoice_number',
        'purchased_at',
        'total_amount',
        'notes',
    ];

    protected $casts = [
        'purchased_at' => 'date',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function getPaidAmountCentsAttribute(): int
    {
        return $this->payments->sum(
            fn (SupplierPayment $payment): int => (int) round((float) $payment->amount * 100),
        );
    }

    public function getBalanceDueCentsAttribute(): int
    {
        return max(0, (int) round((float) $this->total_amount * 100) - $this->paid_amount_cents);
    }
}
