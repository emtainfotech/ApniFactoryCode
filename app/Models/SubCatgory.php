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
        'mid','cid'
    ];
}
