<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\File;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Factory as AuthFactory;

class PlaceController extends Controller
{
    private bool $_pagination = true;

    public function index(Request $request)
    {
        // Order and count
        $collectionQuery = Place::withCount('favorited')
            ->orderBy('created_at', 'desc');

        // Filter?
        if ($search = $request->get('search')) {
            $collectionQuery->where('description', 'like', "%{$search}%");
        }
        
        // Pagination
        $places = $this->_pagination 
            ? $collectionQuery->paginate(5)->withQueryString() 
            : $collectionQuery->get();


        return response()->json([
            'success' => true,
            'data'    => $places 
        ], 200);
    }

    public function store (Request $request)
    {
        $validatedData = $request->validate([
            'name'        => 'required',
            'description' => 'required',
            'upload'      => 'required|mimes:gif,jpeg,jpg,png,mp4|max:2048',
            'latitude'    => 'required',
            'longitude'   => 'required',
        ]);
        
        // Obtenir dades del formulari
        $name        = $request->get('name');
        $description = $request->get('description');
        $upload      = $request->file('upload');
        $latitude    = $request->get('latitude');
        $longitude   = $request->get('longitude');

        // Desar fitxer al disc i inserir dades a BD
        $file = new File();
        $fileOk = $file->diskSave($upload);
        
        if ($fileOk) {
            // Desar dades a BD

            $place = Place::create([
                'name'        => $name,
                'description' => $description,
                'file_id'     => $file->id,
                'latitude'    => $latitude,
                'longitude'   => $longitude,
                'author_id'   => (auth()->user()->id) ? : 1,
            ]);

            // Patró PRG amb missatge d'èxit
            return response()->json([
                'success' => true,
                'data'    => $place
            ], 200);
        } else {
            // Patró PRG amb missatge d'error
            return response()->json([
                'success' => false,
                'message' => 'arxiu no trobat'
            ], 404);
        }
        
    }

    public function show(string $placeID)
    {
        $place = Place::find($placeID);
        return response()->json([
            'success' => true,
            'data'    => $place
        ], 200); 
    }

    public function update(Request $request, string $placeID)
    {
        $place = Place::find($placeID);
        // Validar dades del formulari
        $validatedData = $request->validate([
            'name'        => 'required',
            'description' => 'required',
            'upload'      => 'nullable|mimes:gif,jpeg,jpg,png,mp4|max:2048',
            'latitude'    => 'required',
            'longitude'   => 'required',
        ]);
        
        // Obtenir dades del formulari
        $name        = $request->get('name');
        $description = $request->get('description');
        $upload      = $request->file('upload');
        $latitude    = $request->get('latitude');
        $longitude   = $request->get('longitude');

        // Desar fitxer (opcional)
        if (is_null($upload) || $place->file->diskSave($upload)) {
            // Actualitzar dades a BD
            \Log::debug("Updating DB...");
            $place->name        = $name;
            $place->description = $description;
            $place->latitude    = $latitude;
            $place->longitude   = $longitude;
            $place->save();
            \Log::debug("DB storage OK");
            // Patró PRG amb missatge d'èxit
            return response()->json([
                'success' => true,
                'data'    => $place
            ], 200); 
        } else {
            // Patró PRG amb missatge d'error
            return response()->json([
                'success' => false,
                'message' => 'arxiu no trobat'
            ], 404);
        }
    }

    public function favorite(string $placeID) 
    {
        $fav = Favorite::create([
            'user_id'  => (auth()->user()->id) ? : 1,
            'place_id' => $placeID
        ]);

        if ($fav){
            return response()->json([
                'success' => true,
                'data'    => $fav
            ], 200); 
        } else {
            return response()->json([
                'success' => false,
                'message' => 'arxiu no trobat'
            ], 404);
        };
    }

    public function unfavorite(string $placeID) 
    {

        $fav = Favorite::where([
            
            ['user_id',  '=', (auth()->user()->id) ? : 1],
            ['place_id', '=', $placeID],
        ])->first();
        
        $fav->delete();
        
        if ($fav){
            return response()->json([
                'success' => true,
                'data'    => $fav
            ], 200); 
        } else {
            return response()->json([
                'success' => false,
                'message' => 'arxiu no trobat'
            ], 404);
        };
    }

    public function update_workaround(Request $request, $id)
    {
        return $this->update($request, $id);
    }
}
