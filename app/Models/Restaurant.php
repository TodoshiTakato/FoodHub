<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Traits\UsesUtcTimestamps;
use App\Enums\StatusEnum;

class Restaurant extends Model
{
    use HasFactory, UsesUtcTimestamps;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'phone',
        'email',
        'address',
        'latitude',
        'longitude',
        'timezone',
        'currency',
        'languages',
        'default_language',
        'business_hours',
        'delivery_zones',
        'settings',
        'status',
        'verified_at',
    ];

    protected $casts = [
        'status' => StatusEnum::class,
        'address' => 'array',
        'languages' => 'array',
        'business_hours' => 'array',
        'delivery_zones' => 'array',
        'settings' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'verified_at' => 'datetime',
    ];

    // Relationships - будем добавлять по мере создания других моделей
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    // Scopes для удобных запросов
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }
}
