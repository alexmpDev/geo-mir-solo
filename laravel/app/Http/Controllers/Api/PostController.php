<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Post;
use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Like;
use App\Models\User;

class PostController extends Controller
{
    private bool $_pagination = true;

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Order and count
        $collectionQuery = Post::withCount('liked')
            ->orderBy('created_at', 'desc');
        
        // Filter?
        if ($search = $request->get('search')) {
            $collectionQuery->where('body', 'like', "%{$search}%");
        }
        
        // Pagination
        $posts = $this->_pagination 
            ? $collectionQuery->paginate(5)->withQueryString() 
            : $collectionQuery->get();
        
        return response()->json([
            "success" => true,
            "data" => $posts, 
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    { 
        return view("posts.create");  
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {
        Log::debug("Store post using API");

        // Validar dades del formulari
        $validatedData = $request->validate([
            'body'      => 'required',
            'upload'    => 'required|mimes:gif,jpeg,jpg,png,mp4|max:2048',
            'latitude'  => 'required',
            'longitude' => 'required',
        ]);

        // Obtenir dades del formulari
        $body          = $request->get('body');
        $upload        = $request->file('upload');
        $latitude      = $request->get('latitude');
        $longitude     = $request->get('longitude');

        // Desar fitxer al disc i inserir dades a BD
        $file = new File();
        $fileOk = $file->diskSave($upload);


        if ($fileOk) {
            // Desar dades a BD
            Log::debug("Saving post at DB...");
            $post = Post::create([
                'body'      => $body,
                'file_id'   => $file->id,
                'latitude'  => $latitude,
                'longitude' => $longitude,
                'author_id' => (auth()->user()->id) ? : 1,
            ]);
            Log::debug("DB storage OK");
            // Patró PRG amb missatge d'èxit
            return response()->json([
                "success" => true,
                "data" => $post, 
            ], 200);
        } else {
            return response()->json([
                "success" => false,
                "message" => 'Archivo no encontrado' 
            ], 200);
        }
    }
    
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */

    public function show(string $post_id)
    {
        $post = Post::find($post_id);
        // Count
        $post->loadCount('liked');

        return response()->json([
            "success" => true,
            "data" => $post, 
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    // public function edit($id)
    // {
    //     $post = "???";
    //     $this->authorize($post);

    //     return view("posts.edit", [
    //         'post'   => $post,
    //         'file'   => $post->file,
    //         'author' => $post->user,
    //     ]);
    // }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, string $post_id)
    {
        $post = Post::find($post_id);
        Log::debug("Update post using API");
        // Validar dades del formulari
        $validatedData = $request->validate([
            'body'      => 'required',
            'upload'    => 'nullable|mimes:gif,jpeg,jpg,png,mp4|max:2048',
            'latitude'  => 'required',
            'longitude' => 'required',
        ]);

        // Obtenir dades del formulari
        $body      = $request->get('body');
        $upload    = $request->file('upload');
        $latitude  = $request->get('latitude');
        $longitude = $request->get('longitude');

        // Desar fitxer (opcional)
        if (is_null($upload) || $post->file->diskSave($upload)) {
            // Actualitzar dades a BD
            Log::debug("Updating DB...");
            $post->body      = $body;
            $post->latitude  = $latitude;
            $post->longitude = $longitude;
            $post->save();
            Log::debug("DB storage OK");
            // Patró PRG amb missatge d'èxit
            return response()->json([
                "success" => true,
                "data" => $post, 
            ], 200);
        } else {
            // Patró PRG amb missatge d'error
            return response()->json([
                "success" => false,
                "message" => 'Archivo no encontrado' 
            ], 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(string $post_id)
    {

        $post = Post::find($post_id);
        // Eliminar post de BD
        $post->delete();
        // Eliminar fitxer associat del disc i BD
        $post->file->diskDelete();
        // Patró PRG amb missatge d'èxit
        return redirect()->route("posts.index")
            ->with('success', __(':resource successfully deleted', [
                'resource' => __('Post')
            ]));
    }

    /**
     * Confirm specified resource deletion from storage.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function delete(string $post_id)
    {
        $post = Post::find($post_id);
        
        return response()->json([
            "success" => true,
            "data" => $post, 
        ], 200);
    }

    /**
     * Add like
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function like(string $post_id) 
    {

        $post = Post::find($post_id);

        $like = Like::create([
            'user_id'  => (auth()->user()->id) ? : 1,
            'post_id' => $post_id
        ]);

        return response()->json([
            "success" => true,
            "data" => $post, 
        ], 200);

        return redirect()->back()
            ->with('success', __(':resource successfully saved', [
                'resource' => __('Like')
            ]));
    }
    /**
     * Undo like
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function unlike(string $post_id) 
    {
        $post = Post::find($post_id);

        $like = Like::where([
            ['user_id', '=', (auth()->user()->id) ? : 1],
            ['post_id', '=', $post_id],
        ])->first();
        
        $like->delete();

        return response()->json([
            "success" => true,
            "data" => $post, 
        ], 200);

        return redirect()->back()
            ->with('success', __(':resource successfully deleted', [
                'resource' => __('Like')
            ]));
    }


}
