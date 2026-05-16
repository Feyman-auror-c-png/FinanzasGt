<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditPurchase extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'credit_card_id', 'amount', 'merchant', 'category', 'date', 'paid_at'];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date:Y-m-d',
        'paid_at' => 'datetime',
    ];

    public function creditCard(): BelongsTo
    {
        return $this->belongsTo(CreditCard::class);
    }

    public function scopeCurrentMonth(Builder $query): Builder
    {
        return $query->whereYear('date', now()->year)->whereMonth('date', now()->month);
    }
}
