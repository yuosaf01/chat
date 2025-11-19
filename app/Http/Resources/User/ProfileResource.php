<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ProfileResource extends JsonResource {
    public function toArray(Request $request): array {

        $returned = [
            'name' => $this->name,
            'username' => $this->username,
            'following' => $this->following_count,
            'followers' => $this->followers_count,
            'avatar' => $this->avatar_link,
            'likes' => $this->likes_count,
        ];
        
        if ($authed_user = auth()->user()) {
            if ($authed_user->username != $this->username) {
               $returned['isFollowing'] = $authed_user->isFollowing($this->id);
            }
        }

        return $returned;

    }

}
