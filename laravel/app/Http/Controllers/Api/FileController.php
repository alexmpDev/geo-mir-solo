<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function index()
    {
        $files = File::all();
        return json_encode([
            "success" => "true",
            "data" => $files
        ]);
    }

    public function store(Request $request)
    {
        // Validar fitxer
        $validatedData = $request->validate([
            'upload' => 'required|mimes:gif,jpeg,jpg,png|max:2048'
        ]);
        // Desar fitxer al disc i inserir dades a BD
        $upload = $request->file('upload');
        $file = new File();
        $ok = $file->diskSave($upload);


        if ($ok) {
            return response()->json([
                'success' => true,
                'data'    => $file
            ], 201);
        } else {
            return response()->json([
                'success'  => false,
                'message' => 'Error uploading file'
            ], 500);
        }
    }

    public function show(string $fileID)
    {

        $file = File::find($fileID);

        if (!$file) {
            return response()->json([
                'success'  => false,
                'message' => 'Error show file'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $file
        ], 200);
    }

    public function update(Request $request, string $fileID)
    {
        $file = File::find($fileID);

        if (empty($file)) {
            return response()->json([
                'success' => false,
                'message' => 'arxiu no trobat'
            ], 404);
        }
        // Validar fitxer
        $validatedData = $request->validate([
            'upload' => 'required|mimes:gif,jpeg,jpg,png|max:2048'
        ]);


        // Desar fitxer al disc i actualitzar dades a BD
        $upload = $request->file('upload');
        $ok = $file->diskSave($upload);

        if ($ok) {
            // Patró PRG amb missatge d'èxit
            return json_encode([
                "success" => "true",
                "data" => $file,
            ]);
        }
    }

    public function destroy(string $fileID)
    {
        $file = File::find($fileID);
        if (empty($file)) {
            return response()->json([
                'success' => false,
                'message' => 'arxiu no trobat'
            ], 404);
        }
        $file->diskDelete();

        return response()->json([
            'success' => true,
            'data'    => $file
        ], 201);
    }

    public function update_workaround(Request $request, $id)
    {
        return $this->update($request, $id);
    }
}
