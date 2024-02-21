<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{

    public function index() 
    {

        $review = Review::all();
        return json_encode([
            "success" => "true",
            "data" => $review
        ]);
        
    }

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

        if ($review){
            return response()->json([
                'success' => true,
                'data'    => $review
            ], 200); 
        } else {
            return response()->json([
                'success' => false,
                'message' => 'arxiu no trobat'
            ], 404);
        };
    }

    public function unReview($placeID) 
    {

        $review = Review::where([
            
            ['user_id',  '=', (auth()->user()->id) ? : 1],
            ['place_id', '=', $placeID],
        ])->first();
        
        $ok = $review->delete();
        
        if ($ok){
            return response()->json([
                'success' => true,
                'data'    => $review
            ], 200); 
        } else {
            return response()->json([
                'success' => false,
                'message' => 'arxiu no trobat'
            ], 404);
        };
    }
}
