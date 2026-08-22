<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoxPacking extends Model
{
    use HasFactory;
     protected $fillable = [
        'name',
        'pcs',
        'status','maincategory_id'
    ];
    
    public function maincategory(){
        return $this->belongsTo(MainCategory::class);
    }
}
