<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 
        'description',
        'image',
        'content', 
        'slug', 
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
