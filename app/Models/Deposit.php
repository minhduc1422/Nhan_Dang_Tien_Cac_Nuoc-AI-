<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    protected $table = 'deposits';

    protected $fillable = [
        'user_id',
        'amount',
        'tokens',
        'status',
        'proof_image',
    ];

    protected $casts = [
        'amount' => 'integer',
        'tokens' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}