<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;
use App\Http\Resources\Post\PostResource;
use App\Http\Resources\User\ProfileResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller {

    public function index() {

        $posts = Post::select(Post::public_fields())->where('comment_to',null)->get();

        return response()->json(['posts' => PostResource::collection($posts)],200);

    }

    public function get($id) {

        $post = Post::select(Post::public_fields(['comment_to']))->where('public_id', $id)->first();

        if ($post === null) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        return response()->json(['post' => PostResource::make($post)]);

    }

    public function user_posts($username) {

        $user = User::select(['id'])->where('username', $username)->first();

        if ($user === null) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $posts = $user->posts()->select(Post::public_fields(['comment_to']))->get();

        return response()->json(['posts' => PostResource::collection($posts)],200);

    }

    public function create(Request $request) {

        $validator = $this->validate_post($request);

        if($validator === true) {
            $post = $this->create_post($request);
            return response()->json($post['data'], $post['code']);
        }

        return response()->json($validator, 400);
    }

    public function post_comments($id) {

        $comments = Post::select(Post::public_fields())->where('comment_to', $id)->get();

        return response()->json(['comments' => PostResource::collection($comments)],200);

    }

    private function create_post($request) {

        $postData = [
            'content' => $request['content'],
            'public_id' => $this->generateRandomString(),
            'user_id' => auth()->user()->id,
        ];

        if (isset($request['comment_to'])) {
            $postData['comment_to'] = $request['comment_to'];
        }

        if (isset($request['image'])) {

            $imageFile = $request->file('image');
            $imageName = $this->generateRandomString().".".$imageFile->getClientOriginalExtension();

            if (Storage::putFileAs("uploads/$postData[public_id]", $imageFile,$imageName)) {

                $postData['image'] = "uploads/$postData[public_id]/$imageName";

            }else {
                return ['code' => 500, 'data' => ['message' => 'Failed to upload image']];
            }

        }

        if ($post = Post::create($postData)) {

            return ['code' => 200, 'data' => ['post' => PostResource::make($post)]];

        }

        if (isset($request['image'])) {
            Storage::deleteDirectory("uploads/$postData[public_id]");
        }

        return ['code' => 500, 'data' => ['message' => 'Failed to create post']];

    }

    private function validate_post($request) {

        $rules = [
            'content' => 'required|string|max:200',
        ];

        $messages = [
            'content.required' => 'يجب ادخال نص',
            'content.string' => 'يجب ادخال نص',
            'content.max' => 'يجب ان يكون النص اقل من 200 حرف',
        ];

        if(isset($request['image'])) {
            $rules['image'] = 'required|file|max:20480|mimes:jpeg,jpg,png';
            $messages = $messages + [
                    'image.required' => 'يجب اختيار صورة',
                    'image.file' => 'يجب اختيار صورة',
                    'image.mimes' => 'يجب ان تكون صيغة الصورة احد الصيغ التالية (jpeg,jpg,png)',
                    'image.max' => 'يجب ان يكون حجم الصورة لايتعدى 20MB',
                    'image.uploaded' => 'يجب ان يكون حجم الصورة لايتعدى 20MB',
                ];
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if($validator->fails()) {
            return ['errors' => $validator->errors()];
        }

        return true;
    }

    public function delete($id) {

        $post = Post::where('public_id', $id)->first();

        if ($post === null) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        if ($post->user_id == auth()->user()->id) {
            $post->delete();

            return response()->json(['message' => 'Post deleted successfully'], 200);
        }

        return response()->json(['message' => 'Access denied'], 403);

    }

    public function like($id) {

        $post = Post::where('public_id', $id)->first();

        if ($post === null) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $user = auth()->user();
        $user_likes = $user->likes();

        $like = $user_likes->where('post_id', $post->id);

        if ($like->exists()) {
            $like->first()->delete();
            return response()->json(['message' => 'Unlike successfully'], 200);
        }

        $user_likes->create(['post_id' => $post->id]);
        return response()->json(['message' => 'Liked successfully'], 200);


    }

    public function likes($id) {

        $post = Post::select(['id'])->where('public_id', $id)->first();

        if ($post === null) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $post_likes = $post->likes()->select(['user_id'])->get()->toArray();

        $ids = array_map(function($entry) {
            return $entry['user_id'];
        }, $post_likes);

        $posts = User::select(User::public_fields())->whereIn('id',$ids)->get();

        return response()->json(['users' => ProfileResource::collection($posts)],200);


    }

    public function create_comment(Request $request,$post_id) {

        $post = Post::where('public_id', $post_id)->first();

        if ($post === null) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $request['comment_to'] = $post['public_id'];

        $post = $this->create_post($request);

        return response()->json($post['data'], $post['code']);

    }

    private function generateRandomString($length = 32) {

        $characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $characters.= "abcdefghijklmnopqrstuvwxyz";
        $characters.= "0123456789";
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }


}
