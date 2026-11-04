<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCatgory extends Model
{
    use HasFactory;
      protected $table = 'sub_categories';

     protected $fillable = [
        'title',
        'name',
        'image',
        'status',
        'mid',
        'cid',
        'category_id',
        'maincategory_id',
    ];

    public function category(){
        return $this->belongsTo(Category::class, 'cid');
    }
}
