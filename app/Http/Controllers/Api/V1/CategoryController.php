<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
    public function getCategory()
    {
        try {
            $categories = Category::select('id', 'name')->get();

            return response()->json([
                'success' => true,
                'data' => $categories,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function index()
    {
        try {
            $categories = Category::all();
            if ($categories->isEmpty() ){
                return response()->json([
                    'success' => false,
                    'message' => "Anda belum tambah kategori!",
                    'data' => null,
                ], 404);
            }
            return response()->json([
                'success' => true,
                'message' => 'Categories retrieved successfully',
                'data' => $categories,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching categories',
                'data' => null,
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255|unique:categories,name'
        ]);
    
        try {
            $slug = Str::slug($request->name);
    
            // Cek apakah slug sudah ada
            if (Category::where('slug', $slug)->exists()) {
                $slug .= '-' . time(); // Tambahkan timestamp agar slug unik
            }
    
            $category = Category::create([
                'name' => $request->name,
                'slug' => $slug
            ]);
    
            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil dibuat',
                'data'    => $category
            ], 201);
        } catch (\Illuminate\Database\QueryException $e) {
            // Log::error('Database error while creating category: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Database error occurred while creating the category.',
                'error'   => $e->getMessage(),
            ], 500);
        } catch (Exception $e) {
            // Log::error('General error while creating category: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while creating the category.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
{
    $this->validate($request, [
        'name' => 'required|string|max:255|unique:categories,name,' . $id,
        'slug' => 'nullable|string|max:255|unique:categories,slug,' . $id,
    ]);

    try {
        $category = Category::findOrFail($id);

        // Cek apakah slug sudah ada, jika diubah
        $newSlug = $request->slug ? Str::slug($request->slug) : Str::slug($request->name);
        if (Category::where('slug', $newSlug)->where('id', '!=', $id)->exists()) {
            $newSlug .= '-' . time(); // Tambahkan timestamp agar slug unik
        }

        // Update kategori
        $category->update([
            'name' => $request->name,
            'slug' => $newSlug,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil diperbarui',
            'data'    => $category
        ], 200);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        // Log::error('Category not found: ' . $id);
        return response()->json([
            'success' => false,
            'message' => 'Kategori tidak ditemukan.',
            'error'   => $e->getMessage(),
        ], 404);
    } catch (\Illuminate\Database\QueryException $e) {
        // Log::error('Database error while updating category: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Database error occurred while updating the category.',
            'error'   => $e->getMessage(),
        ], 500);
    } catch (Exception $e) {
        // Log::error('General error while updating category: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'An unexpected error occurred while updating the category.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}


    public function destroy($id)
    {
        try {
            $category = Category::findOrFail($id);
            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully',
                'data' => null,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting category',
                'data' => null,
            ], 500);
        }
    }
}

