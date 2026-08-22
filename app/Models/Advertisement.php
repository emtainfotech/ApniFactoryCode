<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    use HasFactory;
    
    
     protected $fillable = [
       'name', 'content', 'file', 'user_id', 'status', 'created_at', 'updated_at', 'adminmsg', 'screen','startdate','enddate','sequence'
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }
}
