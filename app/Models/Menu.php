<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Traits\UsesUtcTimestamps;
use App\Enums\MenuTypeEnum;

class Menu extends Model
{
    use HasFactory, UsesUtcTimestamps;

    protected $fillable = [
        'restaurant_id',
        'name',
        'slug',
        'description',
        'type',
        'channels',
        'availability_hours',
        'is_active',
        'sort_order',
        'settings',
    ];

    protected $casts = [
        'type' => MenuTypeEnum::class,
        'name' => 'array',
        'description' => 'array',
        'channels' => 'array',
        'availability_hours' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'menu_product')
                    ->withPivot(['sort_order', 'is_featured'])
                    ->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForChannel($query, $channel)
    {
        return $query->whereJsonContains('channels', $channel);
    }
}
