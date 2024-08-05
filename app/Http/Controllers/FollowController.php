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

        if ($isFollowing) {
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

    public function unfollow($username, Request $request)
    {
        $user = User::where('username', $username)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->id == $request->user()->id) {
            return response()->json(['message' => 'You are not allowed to unfollow yourself'], 422);
        }

        $isNotFollowing = $request->user()->followings()->where('following_id', $user->id)->doesntExist();

        if($isNotFollowing){
            return response()->json(['message' => 'You are not following the user'], 422);
        }

        $request->user()->followings()->detach($user->id);

        return response()->json(422);

    }

    public function following($username, Request $request){
        $user = User::where('username', $username)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $formated = $user->followings->map(function($item){
            return [
                'id' => $item->id,
                'full_name' => $item->full_name,
                'username' => $item->username,
                'bio' => $item->bio,
                'is_private' => $item->is_private,
                'created_at' => $item->created_at,
                'is_accepted' => $item->pivot->is_accepted
            ];
        });

        return response()->json(['following' => $formated], 200);

    }

    public function accept($username, Request $request){

        $user = User::where('username', $username)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if($user->id == $request->user()->id){
            return response()->json(['message' => 'You are cannot accept your own follow request'], 422);
        }

        $baseQuery = $user->followers()->where('follower_id', $request->user()->id);

        $isFollowing = $baseQuery->exists();

        if(!$isFollowing){
            return response()->json(['message' => 'The user is not following you'], 422);
        }

        $followRequest = $baseQuery->wherePivot('is_accepted', 0)->first();


        if(!$followRequest){
            return response()->json(['message' => 'Follow request is already accepted']);
        }

        $user->followers()->updateExistingPivot($request->user()->id, ['is_accepted' => true]);


        return response()->json(['message' => 'Follow request accepted'], 200);

    }

    public function followers($username, Request $request){
        $user = User::where('username', $username)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $formated = $user->followers->map(function($item){
            return [
                'id' => $item->id,
                'full_name' => $item->full_name,
                'username' => $item->username,
                'bio' => $item->bio,
                'is_private' => $item->is_private,
                'created_at' => $item->created_at,
                'is_accepted' => $item->pivot->is_accepted
            ];
        });

        return response()->json(['followers' => $formated], 200);

    }

    public function users(Request $request){

        $followingUsersID = $request->user()->followings()->pluck('following_id');

        $listNotFollowingUser = User::whereNotIn('id', $followingUsersID)->where('id', '!=', $request->user()->id)->get();

        return response()->json(['users' => $listNotFollowingUser], 200);
    }

    public function userDetail($username){
        $user = User::where('username', $username)->withCount('followers', 'followings', 'posts')->with('posts.postAttachments')->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return $user;


    }

    public function status(bool $status)
    {
        return $status ? 'Following' : 'Requested';
    }
}
