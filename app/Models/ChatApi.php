<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class ChatApi extends Model
{
    protected $table = 'chat_api'; // Chỉ định rõ tên bảng
    protected $fillable = ['model_name', 'api_key'];

    public function setApiKeyAttribute($value)
    {
        $this->attributes['api_key'] = Crypt::encryptString($value);
    }

    public function getApiKeyAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value; // Trả về giá trị gốc nếu không giải mã được
        }
    }
}