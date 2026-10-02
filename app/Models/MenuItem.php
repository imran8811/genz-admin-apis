<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItem extends Model
{
    protected $fillable = [
        'category_id', 'slug', 'name', 'description', 'price_type',
        'price', 'prices', 'pizza_selection', 'deal_extras', 'default_size',
        'tag', 'is_special', 'is_signature', 'is_active', 'sort_order', 'image_updated_at',
    ];

    protected $casts = [
        // Only the primary key is auto-cast; without this, PDO with emulated prepares
        // (production) serialises the foreign key as a string.
        'category_id' => 'integer',
        'prices' => 'array',
        'pizza_selection' => 'array',
        'deal_extras' => 'array',
        'is_special' => 'boolean',
        'is_signature' => 'boolean',
        'is_active' => 'boolean',
        'image_updated_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
