<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Metadata extends Model
{
    protected $table = 'metadata';
    protected $fillable = [
        'site_title',
        'site_description',
        'site_keywords',
        'favicon',
        'og_image',
        'author',
    ];
}