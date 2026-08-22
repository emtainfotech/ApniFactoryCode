<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\MainCategory;
class Brand extends Model
{
    use HasFactory;
     protected $fillable = [
        'company_id',
        'name',
        'status',
        'addby',
        'image',
        'updated_at', 'user_id', 'mid', 'category_id', 'image', 'trademarkno', 'file', 'type', 'adminresponse'
    ];
    public function company(){
        return $this->belongsTo(Company::class);
    }
    public function company_ineditpage(){
        return $this->belongsTo(Company::class, 'company_id');
    }
    public function maincategory(){
        return $this->belongsTo(Maincategory::class, 'mid');
    }
    public function category(){
        return $this->belongsTo(Category::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
  public function getRouteKeyName()
{
    return 'company_id';
}
}
