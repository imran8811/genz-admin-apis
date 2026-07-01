<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'slug', 'name', 'type', 'sizes', 'is_coming_soon', 'is_active', 'sort_order', 'image_updated_at',
    ];

    protected $casts = [
        'sizes' => 'array',
        'is_coming_soon' => 'boolean',
        'is_active' => 'boolean',
        'image_updated_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** True when this category holds deals (feed convention: slug ends in "deals"). */
    public function isDealGroup(): bool
    {
        return str_ends_with($this->slug, 'deals');
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }
}
