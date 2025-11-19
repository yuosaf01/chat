<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Http\Resources\User\ProfileResource;
use App\Http\Resources\Post\PostResource;

class UserController extends Controller {

    public function __construct() {
        $this->middleware('auth:api');
    }

    public function profile($username = null){

        if ($username == null) {

            return response()->json(['user' => ProfileResource::make(auth()->user())],200);

        }

        $user = User::select(User::public_fields())->where('username', $username)->first();

        if ($user == null) {
            return response()->json(['error' => 'User not found'], 404);
        }


        return response()->json(['user' => ProfileResource::make($user)],200);


    }

    public function likes($username){

        $user = User::select(['id'])->where('username', $username)->first();

        if ($user == null) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user_likes = $user->likes()->select(['post_id'])->get()->toArray();

        $ids = array_map(function($entry) {
            return $entry['post_id'];
        }, $user_likes);

        $posts = Post::select(Post::public_fields())->whereIn('id',$ids)->get();

        return response()->json(['posts' => PostResource::collection($posts)],200);

    }

}
