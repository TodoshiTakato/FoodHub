<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Traits\UsesUtcTimestamps;

class OrderItem extends Model
{
    use HasFactory, UsesUtcTimestamps;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_sku',
        'quantity',
        'unit_price',
        'total_price',
        'modifiers',
        'special_instructions',
        'metadata',
    ];

    protected $casts = [
        'product_name' => 'array', // JSON поле с переводами
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'modifiers' => 'array',
        'metadata' => 'array',
    ];

    // Relationships
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Helper methods
    public function calculateTotal()
    {
        $baseTotal = $this->unit_price * $this->quantity;
        $modifiersTotal = 0;

        if ($this->modifiers) {
            foreach ($this->modifiers as $modifier) {
                $modifiersTotal += ($modifier['price'] ?? 0) * $this->quantity;
            }
        }

        return $baseTotal + $modifiersTotal;
    }
}
