<?php

namespace App\Http\Resources\Post;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\User\ProfileResource;
use App\Models\User;
use App\Models\Post;
class PostResource extends JsonResource {

    public function toArray(Request $request): array {

        $returned = [
            'id' => $this->public_id,
            'content' => $this->content,
            'user' => User::find($this->user_id,['username'])['username'],
            'likes_count' => $this->likes()->count(),
            'liked' => auth()->user()->likes()->where('post_id', $this->id)->exists(),
            'comments' => Post::where('comment_to',$this->public_id)->count(),
            'created_at' => $this->created_at,
        ];

        $nullables = ['image','comment_to'];

        foreach ($nullables as $nullable) {
            if ($this[$nullable]) {
                $returned[$nullable] = $this[$nullable];
            }

        }

//        if ($this->image) {
//            $returned['image'] = $this->image;
//        }
//
//        if ($this->comment_to) {
//            $returned['comment_to'] = $this->comment_to;
//        }

        return $returned;


    }
}
