<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShoppingItem extends Model
{
    use HasFactory;

    protected $fillable = ['shopping_list_id', 'name', 'estimated_price', 'actual_price', 'quantity', 'category', 'checked_at'];

    protected $casts = [
        'estimated_price' => 'decimal:2',
        'actual_price' => 'decimal:2',
        'quantity' => 'integer',
        'checked_at' => 'datetime',
    ];

    public function shoppingList(): BelongsTo
    {
        return $this->belongsTo(ShoppingList::class);
    }
}
