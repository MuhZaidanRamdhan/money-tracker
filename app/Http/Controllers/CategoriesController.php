<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = DB::table('categories')
            ->join('users', 'categories.user_id', '=', 'users.id')
            ->select('categories.id', 'categories.user_id', 'users.name as user_name', 'categories.name', 'categories.type')->get();

        if ($categories->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Categories not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Categories retrieved successfully ',
            'data' => $categories
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validateData = $request->validate([
            'name' => 'required|unique:categories',
            'type' => 'required',
            'user_id' => 'required',
        ]);

        $categories = Categories::create($validateData);
        return response()->json([
            'status' => 'success',
            'message' => 'Categories created successfully ',
            'data' => $categories
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Categories $categories, $id)
    {

        $categories = DB::table('categories')
            ->join('users', 'categories.user_id', '=', 'users.id')
            ->select('categories.id', 'categories.user_id', 'users.name as user_name', 'categories.name', 'categories.type')
            ->where('categories.id', '=', $id)
            ->first();

        if (!$categories) {
            return response()->json([
                'status' => 'error',
                'message' => 'Categories with id ' . $id . ' not found',
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Categories retrieved successfully ',
            'data' => $categories
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Categories $categories, $id)
    {
        $categories = Categories::find($id);

        if (!$categories) {
            return response()->json([
                'status' => 'error',
                'message' => 'Categories with ' . $id . ' not found',
            ], 404);
        }

        $name = $request->name;

        $existingCategory = Categories::where('name', $name)
            ->where('id', '!=', $id)
            ->first();

        if ($existingCategory) {
            return response()->json([
                'status' => 'error',
                'message' => 'The name has already been taken.',
                'errors' => [
                    'name' => ['The name has already been taken.'],
                ],
            ], 422);
        }

        $categories->update([
            'name' => $request->name ?? $categories->name,
            'type' => $request->type ?? $categories->type,
            'user_id' => $request->user_id ?? $categories->user_id
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Categories updated successfully ',
            'data' => $categories
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Categories $categories, $id)
    {
        $categories = Categories::find($id);

        if (!$categories) {
            return response()->json([
                'status' => 'error',
                'message' => 'Categories with ' . $id . ' not found',
            ], 404);
        }

        $categories->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Categories deleted successfully ',
        ]);
    }


    public function userCategories()
    {
        $userId = Auth::id();

        $categories = DB::table('categories')
            ->join('users', 'categories.user_id', '=', 'users.id')
            ->where('user_id', $userId)
            ->select('categories.id', 'categories.name as categories', 'categories.type')
            ->get();

        if ($categories->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Categories not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Categories retrieved successfully ',
            'data' => $categories
        ], 200);
    }

    public function userCategoriesByType(Request $request)
    {
        $userId = Auth::id();

        $type = $request->query('type', 'pemasukan');

        $categories = DB::table('categories')
            ->where('user_id', $userId)
            ->where('type', $type)
            ->select('id', 'name as category', 'type')
            ->get();

        if ($categories->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Categories not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Categories retrieved successfully ',
            'data' => $categories
        ], 200);

    }

    // GET /api/user/categories/{categoryId}/transactions
    public function userCategoryTransactions($categoryId)
    {
        $userId = Auth::id();

        // Pastikan kategori milik user
        $category = DB::table('categories')
            ->where('id', $categoryId)
            ->where('user_id', $userId)
            ->first();

        if (!$category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Category not found',
            ], 404);
        }

        $transactions = DB::table('transactions')
            ->where('transactions.user_id', $userId)
            ->where('transactions.category_id', $categoryId)
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->orderBy('date', 'desc')
            ->select('transactions.id', 'transactions.amount', 'transactions.date', 'transactions.note', 'categories.name as categories')
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Transactions retrieved by category',
            'data' => $transactions
        ], 200);
    }


    public function userCategoriesShow(Request $request, $id)
    {
        $userId = Auth::id();

        $category = DB::table('categories')
            ->join('users', 'categories.user_id', '=', 'users.id')
            ->where('user_id', $userId)
            ->where('categories.id', $id)
            ->select('categories.id', 'categories.name as categories', 'categories.type')
            ->first();

        if (!$category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Categories not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Categories retrieved successfully ',
            'data' => $category
        ], 200);
    }

    public function userCategoriesStore(Request $request)
    {
        $userId = Auth::id();

        $validated = $request->validate([
            'name' => [
                'required',
                Rule::unique('categories')->where(function ($query) use ($userId) {
                    return $query->where('user_id', $userId);
                })
            ],
            'type' => 'required'
        ]);

        $validated['user_id'] = $userId;

        $categories = Categories::create($validated);
        return response()->json([
            'status' => 'success',
            'message' => 'Categories created successfully ',
            'data' => $categories
        ], status: 200);
    }

    public function userCategoriesUpdate(Request $request, $id)
    {
        $userId = Auth::id();

        $category = DB::table('categories')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Categories not found',
            ], 404);
        }

        $newName = $request->name ?? $category->name;

        // Cek apakah nama kategori sudah ada untuk user ini (kecuali yang sedang diedit)
        $existing = DB::table('categories')
            ->where('user_id', $userId)
            ->where('name', $newName)
            ->where('id', '!=', $id)
            ->first();

        if ($existing) {
            return response()->json([
                'status' => 'error',
                'message' => 'You already have a category with that name.',
            ], 422);
        }

        $input = [
            'name' => $newName,
            'type' => $request->type ?? $category->type
        ];

        DB::table('categories')
            ->where('id', $id)
            ->update($input);

        $updatedCategory = DB::table('categories')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        return response()->json([
            'status' => 'success',
            'message' => 'Categories updated successfully',
            'data' => $updatedCategory
        ], 200);
    }

    public function userCategoriesDestroy(Request $request, $id)
    {
        $userId = Auth::id();

        $category = DB::table('categories')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Categories not found',
            ], 404);
        }

        DB::table('categories')
            ->where('id', $id)
            ->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Categories deleted successfully ',
        ], status: 200);
    }
}
