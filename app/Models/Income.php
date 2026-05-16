<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Income extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'type', 'label', 'amount', 'date'];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date:Y-m-d',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeCurrentMonth(Builder $query): Builder
    {
        return $query->whereYear('date', now()->year)->whereMonth('date', now()->month);
    }
}
