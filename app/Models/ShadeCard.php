<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShadeCard extends Model
{
    use HasFactory;
     protected $fillable = [
        'hexcode',
        'name',
        'status',
        'subcategoryid',
        'image','user_id','adminmsg', 'maincategory_id', 'category_id'
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }
    
    public function maincategory(){
        return $this->belongsTo(MainCategory::class);
    }
    public function category(){
        return $this->belongsTo(Category::class);
    }
}
