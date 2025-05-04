<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'File size exceeds 2mb.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $path = $request->file('image')->store('images', 'public');

        return response()->json([
            'message' => 'Upload success',
            'path' => Storage::url($path)
        ]);
    }
}
