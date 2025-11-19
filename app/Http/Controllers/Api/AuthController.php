<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\HasApiTokens;
use App\Models\User;
use App\Http\Resources\User\ProfileResource;

class AuthController extends Controller
{
//    public function __construct()
//    {
//        $this->middleware('auth:api');
//    }

    public function register(Request $request) {

        $dataRegister = [
            'name' => $request['name'],
            'username' => $request['username'],
            'email' => $request['email'],
            'password' => $request['password'],
            'password_confirmation' => $request['password_confirmation'],
        ];


        $rules = [
            'name' => ['required', 'string', 'max:50'],
            'username' => ['required', 'string', 'max:32', 'min:3', 'regex:/^[-a-zA-Z0-9_.]+$/', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:180', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'max:64', 'confirmed','regex:/^[-a-zA-Z0-9!@]+$/'],
        ];

        $messages = [
            'name.required' => 'يجب ادخال اسم',
            'name.string' => 'يجب ادخال اسم',
            'name.max' => 'يجب ان يكون الاسم اقل من 50 حرف',
            'username.required' => 'يجب ادخال اسم المستخدم',
            'username.string' => 'يجب ادخال اسم المستخدم',
            'username.unique' => 'اسم المستخدم موجود مسبقاً',
            'username.max' => 'يجب ان يكون اسم المستخدم اقل من 32 حرف',
            'username.min' => 'يجب ان يكون اسم المستخدم اكثر من 3 أحرف',
            'username.regex' => 'استخدم احرف انقليزية وارقام والرموز التالية (- _ .)',
            'email.required' => 'يجب ادخال بريد اكتروني',
            'email.string' => 'يجب ادخال بريد اكتروني',
            'email.email' => 'البريد الاكتروني غير صالح',
            'email.max' => 'يجب ان يكون البريد الااكتروني اقل من 180 حرف',
            'email.unique'  => 'هذا البريد الاكتوني مستخدم مسبقاً',
            'password.required' => 'يجب ادخال كلمة سر',
            'password.string' => 'يجب ادخال كلمة سر',
            'password.min' => 'يجب يجب ان تكون كلمة السر اكثر من 8 احرف',
            'password.max' => 'يجب يجب ان تكون كلمة السر اقل من 32 احرف',
            'password.confirmed' => 'يجب ان تكون كلمة السر مطابقة للتأكيد',
            'password.regex' => 'استخدم احرف انقليزية وارقام والرموز الخاصة',

        ];

        $validate = Validator::make($dataRegister, $rules, $messages );

        if($validate->fails()) {

            return response()->json(['errors' => $validate->errors()], 401);

        }

        $dataRegister['password'] = bcrypt($request['password']);

        $user = User::create($dataRegister);

        $accessToken = $user->createToken('AuthToken')->accessToken;

        return response()->json(['user' => ProfileResource::make($user), 'accessToken' => $accessToken], 200);

    }


    public function login(Request $request) {

        $rules = [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];

        $messages = [
            'email.required' => 'يجب ادخال بريد اكتروني',
            'email.string' => 'يجب ادخال بريد اكتروني',
            'email.email' => 'البريد الاكتروني غير صالح',
            'password.required' => 'يجب ادخال كلمة السر',
            'password.string' => 'يجب ادخال كلمة السر',
        ];

        $validate = Validator::make($request->all(), $rules, $messages);

        if ($validate->fails()) {

            return response()->json(['errors' => $validate->errors()], 401);

        }

        if (!Auth()->attempt($request->all())) {

            return response()->json(['message' => 'معلومات الدخول غير صحيحة','status' => false], 401);
        }

        $user = Auth::user();
        $accessToken = $user->createToken('AuthToken')->accessToken;

        return response()->json(['user' =>  ProfileResource::make($user), 'accessToken' => $accessToken], 200);

    }
}
