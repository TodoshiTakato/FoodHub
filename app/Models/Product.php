<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Traits\UsesUtcTimestamps;
use App\Enums\ProductTypeEnum;

class Product extends Model
{
    use HasFactory, UsesUtcTimestamps;

    protected $fillable = [
        'restaurant_id',
        'category_id',
        'name',
        'slug',
        'description',
        'sku',
        'type',
        'images',
        'prices',
        'cost_price',
        'calories',
        'allergens',
        'ingredients',
        'nutritional_info',
        'modifiers',
        'combo_items',
        'channels',
        'availability_hours',
        'is_active',
        'is_featured',
        'sort_order',
        'stock_quantity',
        'track_stock',
        'settings',
    ];

    protected $casts = [
        'type' => ProductTypeEnum::class,
        'name' => 'array',
        'description' => 'array',
        'images' => 'array',
        'prices' => 'array',
        'cost_price' => 'decimal:2',
        'allergens' => 'array',
        'ingredients' => 'array',
        'nutritional_info' => 'array',
        'modifiers' => 'array',
        'combo_items' => 'array',
        'channels' => 'array',
        'availability_hours' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'track_stock' => 'boolean',
        'settings' => 'array',
    ];

    // Relationships
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'menu_product')
                    ->withPivot(['sort_order', 'is_featured'])
                    ->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeForChannel($query, $channel)
    {
        return $query->whereJsonContains('channels', $channel);
    }

    public function scopeInStock($query)
    {
        return $query->where(function($q) {
            $q->where('track_stock', false)
              ->orWhere('stock_quantity', '>', 0);
        });
    }

    // Helper methods
    public function getPriceForChannel($channel = 'web')
    {
        return $this->prices[$channel] ?? $this->prices['web'] ?? 0;
    }
}
