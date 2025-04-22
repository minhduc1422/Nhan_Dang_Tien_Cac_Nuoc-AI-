<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    protected $fillable = ['user_id', 'amount', 'tokens', 'status', 'proof_image'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}