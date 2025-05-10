<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoneyDetection extends Model
{
    protected $table = 'money_detections';
    protected $fillable = ['user_id', 'amount', 'result', 'image', 'status'];
}