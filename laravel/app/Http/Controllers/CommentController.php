<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Comments;
use Egulias\EmailValidator\Parser\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    
    public function comments($post_id, Request $request) 
    {
        $validateData = $request->validate([
            'comments' => 'required',
        ]);

        $comment = Comments::create([
            'user_id'   => (auth()->user()->id) ? : 1,
            'post_id'   => $post_id,
            'comments'  => $request->get('comments')
        ]);

        $post = Post::find($post_id);

        if ($comment){
            return redirect()->route("posts.show", $post);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'el archivo no se ha encontrado'
        ], 404);
        }
    }
    public function delcomments($post_id)
    {

        $comment = Comments::where([
            ['user_id', '=', auth()->user()->id],
            ['post_id', '=', $post_id],
        ])->first();

        $comment->delete();

        return redirect()->back()
            ->with('success', __(':resource successfully deleted', [
                'resource' => __('Comment')
            ]));
    }
}
