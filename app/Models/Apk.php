<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Apk extends Model
{
    protected $table = 'apks';
    protected $fillable = [
        'name',
        'version',
        'description',
        'file_path',
    ];
}