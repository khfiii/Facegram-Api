<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\PostController;


Route::prefix('v1')->group(function(){

    Route::prefix('auth')->group(function(){
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('posts', [PostController::class, 'create']);
        Route::delete('posts/{id}', [PostController::class, 'delete']);
        Route::get('posts', [PostController::class, 'index']);
        Route::post('users/{username}/follow', [FollowController::class, 'follow']);
        Route::delete('users/{username}/unfollow', [FollowController::class, 'unfollow']);
        Route::get('users/{username}/following', [FollowController::class, 'following']);
        Route::put('users/{username}/accept', [FollowController::class, 'accept']);
        Route::get('users/{username}/followers', [FollowController::class, 'followers']);
        Route::get('users', [FollowController::class, 'users']);
        Route::get('users/{username}', [FollowController::class, 'userDetail']);
    });
});
