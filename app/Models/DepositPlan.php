<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepositPlan extends Model
{
    protected $table = 'deposit_plans';

    protected $fillable = [
        'amount',
        'tokens',
        'description',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tokens' => 'integer',
        'is_active' => 'boolean',
    ];
}