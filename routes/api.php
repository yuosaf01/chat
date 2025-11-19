<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FollowController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UserController;

Route::post("register", [AuthController::class, 'register']);
Route::post("login", [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function() {

    Route::prefix('posts')->group(function() {

        Route::get('/',[PostController::class, 'index']);
        Route::post('/',[PostController::class, 'create']);
        Route::get('{id}',[PostController::class, 'get']);
        Route::get('{id}/like',[PostController::class, 'like']);
        Route::get('{id}/likes',[PostController::class, 'likes']);
        Route::post('{id}/comments',[PostController::class, 'create_comment']);
        Route::get('{id}/comments',[PostController::class, 'post_comments']);
        Route::delete('{id}',[PostController::class, 'delete']);

    });

    Route::prefix('profile')->group(function() {

        Route::get('{username?}',[UserController::class, 'profile']);
        Route::get("{username}/follow", [FollowController::class, 'follow']);
        Route::get("{username}/unfollow", [FollowController::class, 'unfollow']);
        Route::get('{username}/posts',[PostController::class, 'user_posts']);
        Route::get('{username}/likes',[UserController::class, 'likes']);

    });

});

