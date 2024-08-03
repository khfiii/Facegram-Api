<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'full_name' => 'required',
            'bio' => 'required|max:100',
            'username' => 'required|min:3|unique:users,username|regex:/^[a-zA-Z0-9._]+$/',
            'password' => 'required|min:6',
            'is_private' => 'boolean',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => 'Invalid field',
                'errors' => $validate->errors()
            ], 422);
        }

        $data = $request->all();

        $user = User::create($data);

        $token = $user->createToken('register')->plainTextToken;

        return response()->json([
            'message' => 'Register success',
            'token' => $token,
            'user' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        if (!auth()->attempt($credentials)) {
            return response()->json([
                'message' => 'Wrong username or password'
            ], 401);
        }

        $user = [
            'message' => 'Login success',
            'token' => $request->user()->createToken('login')->plainTextToken,
            'user' => $request->user()
        ];

        return response()->json($user, 200);

    }

    public function logout(Request $request)
    {

        $delete = $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logout success'
        ]);




    }
}
