<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class FollowController extends Controller
{
    public function __construct() {
        $this->middleware('auth:api');
    }

    public function follow($username) {

        if ($username == auth()->user()->username) {
            return response()->noContent(403);
        }

        $userToFollow = User::select(['id'])->where('username', $username)->firstOrFail();
        auth()->user()->follow($userToFollow);

        return response()->noContent(200);

    }

    public function unfollow($username) {

        if ($username == auth()->user()->username) {
            return response()->noContent(403);
        }

        $userToUnfollow = User::select(['id'])->where('username', $username)->firstOrFail();
        auth()->user()->unfollow($userToUnfollow);

        return response()->noContent(200);
    }

}
