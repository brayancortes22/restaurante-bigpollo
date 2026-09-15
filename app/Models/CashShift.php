<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'opened_at',
        'closed_at',
        'opening_amount',
        'cash_sales',
        'card_sales',
        'transfer_sales',
        'total_sales',
        'expected_cash',
        'actual_cash_counted',
        'difference',
        'status',
        'notes',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'opening_amount' => 'decimal:2',
        'cash_sales' => 'decimal:2',
        'card_sales' => 'decimal:2',
        'transfer_sales' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'actual_cash_counted' => 'decimal:2',
        'difference' => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
