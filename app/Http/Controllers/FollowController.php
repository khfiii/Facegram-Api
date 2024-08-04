<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function follow($username, Request $request){
       $user = User::where('username', $username)->first();

       if(!$user){
        return response()->json(['error' => 'User not found'], 404);
       }

       if($user->id == $request->user()->id){
        return response()->json(['error' => 'You are not allowed to follow yourself'], 422);
       }
    }
}
