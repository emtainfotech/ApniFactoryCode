<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    use HasFactory;
     protected $fillable = [
        'title',
        'image',
        'status','screen','startdate','enddate','company_id','sequence'
    ];
    public function company(){
        return $this->belongsTo(Company::class);
    }
}
