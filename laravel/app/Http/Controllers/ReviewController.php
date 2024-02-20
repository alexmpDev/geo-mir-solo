<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Place;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function review(Request $request, $placeID) 
    {
        $validatedData = $request->validate([
            'message'  => 'required',
        ]);

        $review = Review::create([
            'user_id'  => (auth()->user()->id) ? : 1,
            'place_id' => $placeID,
            'message' => $request->get('message')
        ]);

        $place = Place::find($placeID);
        if ($review){
            return redirect()->route("places.show", $place);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'arxiu no trobat'
            ], 404);
        };
    }

    public function unReview($placeID) 
    {

        $place = Place::find($placeID);
        $review = Review::where([
            
            ['user_id',  '=', (auth()->user()->id) ? : 1],
            ['place_id', '=', $placeID],
        ])->first();
        
        $ok = $review->delete();
        
        if ($ok){
            return redirect()->route("places.show", $place);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'arxiu no trobat'
            ], 404);
        };
    }
}
