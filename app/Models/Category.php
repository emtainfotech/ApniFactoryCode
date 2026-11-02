<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
     protected $fillable = [
        'title',
        'name',
        'image',
        'status',
        'maincategory_id',
        'addby',
        'adminmsg',
        'adminstatus','sequence'
    ];
    
    public function maincategory(){
        return $this->belongsTo(MainCategory::class);
    }

    public function getStatusAttribute($value)
    {
        if (request() && request()->is('api/*')) {
            return in_array(strtolower($value ?? ''), ['active', '1']) ? 1 : 0;
        }
        return $value;
    }
  
}
