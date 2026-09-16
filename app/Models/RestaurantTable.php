<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestaurantTable extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'table_number',
        'capacity',
        'location',
        'pos_x',
        'pos_y',
        'shape',
        'zone',
        'merged_with_table_id',
        'status',
    ];

    public function mergedWith(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'merged_with_table_id');
    }

    public function mergedChildren(): HasMany
    {
        return $this->hasMany(RestaurantTable::class, 'merged_with_table_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function activeOrder()
    {
        return $this->hasOne(Order::class)
            ->whereIn('status', ['pending', 'in_kitchen', 'ready', 'served'])
            ->latestOfMany();
    }
}
