<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function follow($username, Request $request)
    {
        $user = User::where('username', $username)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->id == $request->user()->id) {
            return response()->json(['message' => 'You are not allowed to follow yourself'], 422);
        }

        $isFollowing = $request->user()->followings()->where('following_id', $user->id)->exists();
        $isPublic = !$user->is_private;

        if($isFollowing){
            $followingUser = $request->user()->followings()->where('following_id', $user->id)->first();
            return response()->json([
                'message' => 'You are already followed',
                'status' => $this->status($followingUser->pivot->is_accepted)
            ], );
        }

        $request->user()->followings()->attach($user->id);

        return response()->json([
            'message' => 'Follow success',
            'status' => $this->status($isPublic)
        ], 200);
    }

    public function status(bool $status){
        return $status ? 'Following' : 'Requested';
    }
}
