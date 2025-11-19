<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
    ];

    public function user() {
        $this->belongsTo(User::class);
    }

    public function post() {
        $this->belongsTo(Post::class);
    }

}
