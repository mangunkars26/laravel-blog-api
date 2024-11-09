<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Models\Tag;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TagController extends Controller
{
    public function index()
    {
        try {
            $tags = Tag::all();
            if ($tags->isEmpty()){
                return response()->json([
                    'success' => false,
                    'message' => "Anda belum buat tag",
                    'data' => null,
                ]);
            }
            return response()->json([
                'success' => true,
                'message' => 'Tags berhasil diambil.',
                'data' => $tags,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching tags',
                'data' => null,
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255|unique:tags',
        ]);

        try {
            $slug = Str::slug($request->name);
            $tag = Tag::create([
                'name' => $request->name,
                'slug' => $slug
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tag created successfully',
                'data' => $tag,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating tag: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255|unique:tags,name,' . $id,
            'slug' => 'required|string|max:255|unique:tags,slug,' . $id,
        ]);

        try {
            $tag = Tag::findOrFail($id);
            $tag->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Tag updated successfully',
                'data' => $tag,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating tag: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $tag = Tag::findOrFail($id);
            $tag->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tag deleted successfully',
                'data' => null,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting tag',
                'data' => null,
            ], 500);
        }
    }
}

