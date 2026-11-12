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

    protected static function booted()
    {
        static::saving(function (self $subcat) {
            $catId = (int) ($subcat->cid ?: $subcat->category_id ?: 0);
            if ($catId > 0) {
                $subcat->cid = $catId;
                $subcat->category_id = $catId;
                $cat = Category::find($catId);
                if ($cat && $cat->maincategory_id) {
                    $subcat->mid = (int) $cat->maincategory_id;
                    $subcat->maincategory_id = (int) $cat->maincategory_id;
                }
            }
            if (empty($subcat->title)) {
                $subcat->title = (string) ($subcat->name ?? '');
            }
            if (empty($subcat->image)) {
                $subcat->image = 'default.png';
            }
        });
    }
}
