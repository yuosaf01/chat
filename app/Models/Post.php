<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'content',
        'image',
        'public_id',
        'user_id',
        'comment_to'
    ];

    static public function public_fields(Array $fields = []) {
        return array_merge(['id','public_id', 'user_id','created_at','content','image'],$fields);
    }

    public function user() {
        $this->belongsTo(User::class);
    }

    public function likes() {

        return $this->hasMany(Like::class);
    }

    public function getLikesCountAttribute() {
        return $this->likes()->count();
    }

}
