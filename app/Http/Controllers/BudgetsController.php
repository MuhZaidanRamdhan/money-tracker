<?php

namespace App\Http\Controllers;

use App\Models\Budgets;
use Auth;
use DB;
use Illuminate\Http\Request;

class BudgetsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $budgets = Budgets::join('users', 'budgets.user_id', '=', 'users.id')
            ->join('categories', 'budgets.category_id', '=', 'categories.id')
            ->select('budgets.id', 'users.name', 'categories.name as categories', 'budgets.amount', 'budgets.month')
            ->get();

        if ($budgets->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Budgets not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Budgets retrieved successfully ',
            'data' => $budgets
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validateData = $request->validate([
            'user_id' => 'required',
            'category_id' => 'required',
            'amount' => 'required',
            'month' => 'required',
        ]);

        $budgets = Budgets::create($validateData);
        return response()->json([
            'status' => 'success',
            'message' => 'Budgets created successfully ',
            'data' => $budgets
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $budgets = Budgets::join('users', 'budgets.user_id', '=', 'users.id')
            ->join('categories', 'budgets.category_id', '=', 'categories.id')
            ->select('users.name', 'categories.name as categories', 'budgets.amount', 'budgets.month')
            ->where('budgets.id', '=', $id)
            ->first();

        if (!$budgets) {
            return response()->json([
                'status' => 'error',
                'message' => 'Budgets with id ' . $id . ' not found',
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Budgets retrieved successfully ',
            'data' => $budgets
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $budgets = Budgets::find($id);

        if (!$budgets) {
            return response()->json([
                'status' => 'error',
                'message' => 'Budgets with ' . $id . ' not found',
            ], 404);
        }

        $input = [
            'user_id' => $request->user_id ?? $budgets->user_id,
            'category_id' => $request->category_id ?? $budgets->category_id,
            'amount' => $request->amount ?? $budgets->amount,
            'month' => $request->month ?? $budgets->month,
        ];

        $budgets->update($input);

        return response()->json([
            'status' => 'success',
            'message' => 'Budgets updated successfully ',
            'data' => $budgets
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $budgets = Budgets::find($id);

        if (!$budgets) {
            return response()->json([
                'status' => 'error',
                'message' => 'Budgets with ' . $id . ' not found',
            ], 404);
        }

        $budgets->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Budgets deleted successfully ',
        ]);
    }

    public function userBudgets()
    {
        $userId = Auth::id();

        $budgets = DB::table('budgets')
            ->join('users', 'budgets.user_id', '=', 'users.id')
            ->join('categories', 'budgets.category_id', '=', 'categories.id')
            ->select('budgets.id','budgets.category_id', 'categories.name as categories', 'budgets.amount', 'budgets.month')
            ->where('budgets.user_id', $userId)
            ->get();

        if ($budgets->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Budgets not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Budgets retrieved successfully ',
            'data' => $budgets
        ]);
    }

    public function userBudgetsShow($id)
    {
        $userId = Auth::id();

         $budget = DB::table('budgets')
            ->join('users', 'budgets.user_id', '=', 'users.id')
            ->join('categories', 'budgets.category_id', '=', 'categories.id')
            ->select('budgets.id','budgets.category_id','categories.name as categories', 'budgets.amount', 'budgets.month')
            ->where('budgets.user_id', $userId)
            ->where('budgets.id', $id)
            ->first();


        if (!$budget) {
            return response()->json([
                'status' => 'error',
                'message' => 'Budgets not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Budgets retrieved successfully ',
            'data' => $budget
        ], 200);
    }

    public function userBudgetsStore(Request $request)
    {

        $userId = Auth::id();

        $validated = $request->validate([
            'category_id' => 'required|integer|exists:categories,id',
            'month' => 'required|date_format:Y-m',
            'amount' => 'required|numeric|min:0',
        ]);

        // ✅ Pastikan category_id milik user
        $category = DB::table('categories')
            ->where('id', $validated['category_id'])
            ->where('user_id', $userId)
            ->first();

        if (!$category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid category for this user',
            ], 403);
        }

        $existing = DB::table('budgets')
            ->where('user_id', $userId)
            ->where('category_id', $validated['category_id'])
            ->where('month', $validated['month'])
            ->first();

        if ($existing) {
            return response()->json([
                'error' => 'Budget for this category and month already exists.'
            ], 409);
        }

        $budgetId = DB::table('budgets')->insertGetId([
            'user_id' => $userId,
            'category_id' => $validated['category_id'],
            'month' => $validated['month'],
            'amount' => $validated['amount'],
        ]);

        $budget = DB::table('budgets')->where('id', $budgetId)->first();

        return response()->json([
            'status' => 'success',
            'message' => 'Budget created successfully',
            'data' => $budget
        ], 201);
    }

    public function userBudgetsUpdate(Request $request, $id)
    {
        $userId = Auth::id();

        $budget = DB::table('budgets')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$budget) {
            return response()->json([
                'status' => 'error',
                'message' => 'Budgets not found',
            ], 404);
        }

        $input = [
            'category_id' => $request->category_id ?? $budget->category_id,
            'month' => $request->month ?? $budget->month,
            'amount' => $request->amount ?? $budget->amount,
        ];

        DB::table('budgets')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->update($input);


        $budgetId = DB::table('budgets')->where('id', $id)->first();

        return response()->json([
            'status' => 'success',
            'message' => 'Budgets updated successfully ',
            'data' => $budgetId
        ], 200);
    }

    public function userBudgetsDestroy($id)
    {
        $userId = Auth::id();

        $budget = DB::table('budgets')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$budget) {
            return response()->json([
                'status' => 'error',
                'message' => 'Budgets not found',
            ], 404);
        }

        DB::table('budgets')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Budgets deleted successfully ',
        ], 200);
    }

    public function userBudgetsProgress()
    {
        $userId = Auth::id();

        $budgets = DB::table('budgets')
            ->join('categories', 'budgets.category_id', '=', 'categories.id')
            ->select('budgets.*', 'categories.name as category_name')
            ->where('budgets.user_id', $userId)
            ->get();

        if ($budgets->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No budgets found for this user',
            ], 404);
        }

        $result = [];

        foreach ($budgets as $budget) {
            $totalSpent = DB::table('transactions')
                ->join('categories', 'transactions.category_id', '=', 'categories.id')
                ->where('transactions.user_id', $userId)
                ->where('transactions.category_id', $budget->category_id)
                ->where('transactions.date', 'like', $budget->month . '%')
                ->sum('transactions.amount');

            $result[] = [
                'id' => $budget->id,
                'category_id' => $budget->category_id,
                'category_name' => $budget->category_name,
                'month' => $budget->month,
                'budgeted' => $budget->amount,
                'spent' => $totalSpent,
                'progress' => min(100, ($budget->amount > 0 ? ($totalSpent / $budget->amount) * 100 : 0))
            ];
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Budgets retrieved successfully',
            'data' => $result
        ], 200);

    }

}
