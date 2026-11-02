<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class faq extends Model
{
    use HasFactory;
      protected $fillable = [
        'question',
        'answer',
        'status',
        'user_id'
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }

    public function getStatusAttribute($value)
    {
        if (request()->is('api/*')) {
            return in_array(strtolower((string)$value), ['1', 'active', 'true']) ? 1 : 0;
        }
        return $value;
    }
}
