<?php

namespace App\Http\Controllers\Api;

use App\Models\Comments;
use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index()
    {
        $comment = Comments::all();
        return json_encode([
            "success" => "true",
            "data" => $comment
        ]);
    }
    public function comments(string $post_id, Request $request) 
    {
        $request->validate([
            'comment' => 'required|string',
        ]);

        $post = Post::find($post_id);

        if (!$post) {
            return response()->json([
                "success" => false,
                "message" => "Post not found",
            ], 404);
        }

        $comments = Comments::create([
            'user_id'  => (auth()->user()->id) ? : 1,
            'post_id' => $post_id,
            'comments' => $request->input('comment'),
        ]);

        return response()->json([
            "success" => true,
            "data" => $post, 
            "comment" => $comments,
        ], 200);

        return redirect()->back()
            ->with('success', __(':resource successfully saved', [
                'resource' => __('Comment')
            ]));
    }

    public function delcomments($post_id)
    {

        $comment = Comments::where([
            ['user_id', '=', auth()->user()->id],
            ['post_id', '=', $post_id],
        ])->first();

        $ok = $comment->delete();

        if ($ok){
            return response()->json([
                'success' => true,
                'data'    => $comment
            ], 200); 
        } else {
            return response()->json([
                'success' => false,
                'message' => 'arxiu no trobat'
            ], 404);
        };
    }
}
