<?php

namespace App\Http\Controllers;


use App\Models\Post;
use Illuminate\Http\Request;
use App\Models\PostAttachment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{

    public function index(Request $request){

        $credentials = $request->only('page', 'size');

        $validate = Validator::make($credentials, [
            'page' => 'integer|min:0',
            'size' => 'integer|min:1|'
        ]);

        if($validate->fails()){
            return response()->json([
                'message' => 'Invalid fields',
                'error' => $validate->errors()
            ], 422);
        }

        $page = $request->query('page', 0);
        $size = $request->query('size', 10);

        $posts = Post::with('postAttachments')->paginate($size, ['*'], 'page', $page);

        return response()->json([
            'page' => $page,
            'size' => $size,
            'posts' => $posts->items()
        ], 200);




    }

    public function create(Request $request)
    {

        $credentials = $request->only('caption', 'attachments');

        $validate = Validator::make($credentials, [
            'caption' => 'required',
            'attachments' => 'required|array',
            'attachments.*' => 'image|mimes:png, jpg, jpeg, webp'
        ]);

            if($validate->fails()){
                return response()->json([
                    'message' => 'invalid field',
                    'errors' => $validate->errors()
                ], 422);
            }

        try {
            DB::beginTransaction();

           $post = new Post();
           $post->caption = $request->caption;
           $post->user_id = $request->user()->id;
           $post->save();


            foreach($request->attachments as $attachment){
                $postAttachment = new PostAttachment();
                $postAttachment->storage_path = 'posts/'.$attachment->getClientOriginalName();
                $postAttachment->post_id = $post->id;
                $postAttachment->save();
                $attachment->store('public');
            }

            DB::commit();

            return response()->json([
                'message' => 'Create post success'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function delete($id, Request $request)
    {
       $post = Post::find($id);

       if(is_null($post)){
        return response()->json(['message' => 'Post not found'], 404);
       }


       if($post->user_id != $request->user()->id){
        return response()->json([
            "message" => "Forbidden access"
        ], 403);
       }

       $post->delete();

       return response()->json([], 204);

    }
}
