<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;
     protected $fillable = [
            'name',
            'slug',
            'description',
            'status',
            'user_id'
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }

    public function getStatusAttribute($value)
    {
        if (request() && request()->is('api/*')) {
            return in_array(strtolower($value ?? ''), ['active', '1']) ? 1 : 0;
        }
        return $value;
    }
}
