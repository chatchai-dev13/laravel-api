<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class BlogController extends Controller
{
    public function index()
    {
        $blogs = Blog::all();

        return response()->json([
            'item' => $blogs,
            'path' => 'storage/images',
            'url' => asset('storage/images/')
        ]);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // $path = $request->file('image')->store('images', 'public');
        $base64Image = request()->input('image');
        $image_name;

        if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $matches)) {
            $extension = strtolower($matches[1]);
            $base64Image = preg_replace('/^data:image\/\w+;base64,/', '', $base64Image);
            $imageData = base64_decode($base64Image);

            $sizeInBytes = strlen($imageData);
            $maxSize = 2 * 1024 * 1024; // 2MB

            if ($sizeInBytes > $maxSize) {
                return response()->json(['error' => 'File size is larger than 2MB.'], 400);
            }

            $fileName = 'image_' . time() . '.' . $extension;
            Storage::disk('public')->put('images/' . $fileName, $imageData);

            $image_name = $fileName;
        }

        $title = $request->title;
        $slug = trim(preg_replace('/[^ก-๙a-zA-Z0-9]+/u', '-', $title), '-');
        $slug = strtolower($slug);

        $data = Blog::create([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $fileName,
            'content' => $request->content,
            'slug' => $slug,
            'user_id' => $request->user()->id,
        ]);

        return response()->json($data);
    }

    public function show(string $slug)
    {
        $blog = Blog::where('slug', $slug)->first();

        if (!$blog) {
            return response()->json(['error' => 'Blog not found.'], 404);
        }

        return response()->json($blog);
    }

    public function edit(string $id)
    {
        $blog = Blog::where('id', $id)->first();

        if (!$blog) {
            return response()->json(['error' => 'ID not found.'], 404);
        }

        return response()->json($blog);
    }

    public function update(Request $request, string $id)
    {
        $blog = Blog::where('id', $id)->first();

        if (!$blog) {
            return response()->json(['error' => 'ID not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'content' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $base64Image = request()->input('image');
        $image_name;

        if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $matches)) {
            $extension = strtolower($matches[1]);
            $base64Image = preg_replace('/^data:image\/\w+;base64,/', '', $base64Image);
            $imageData = base64_decode($base64Image);

            $sizeInBytes = strlen($imageData);
            $maxSize = 2 * 1024 * 1024; // 2MB

            if ($sizeInBytes > $maxSize) {
                return response()->json(['error' => 'File size is larger than 2MB.'], 400);
            }
        
            $fileName = 'image_' . time() . '.' . $extension;
            Storage::disk('public')->put('images/' . $fileName, $imageData);

            $image_name = $fileName;

            Storage::disk('public')->delete('images/' . $blog->image);
        } else {
            $image_name = $blog->image;
        }

        $title = $request->title;
        $slug = trim(preg_replace('/[^ก-๙a-zA-Z0-9]+/u', '-', $title), '-');
        $slug = strtolower($slug);

        $blog->update([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $image_name,
            'content' => $request->content,
            'slug' => $slug
        ]);

        return response()->json(['message' => 'Successfully updated.']);
    }

    public function destroy(string $id)
    {
        $blog = Blog::where('id', $id)->first();

        if (!$blog) {
            return response()->json(['error' => 'ID not found.'], 404);
        }

        Storage::disk('public')->delete('images/' . $blog->image);
        Blog::where('id', $id)->delete();

        return response()->json(['message' => 'Successfully deleted.']);
    }
}