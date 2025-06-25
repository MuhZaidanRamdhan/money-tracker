<?php

namespace App\Http\Controllers;

use App\Models\SavingsGoals;
use Auth;
use DB;
use Illuminate\Http\Request;

class SavingsGoalsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $savings_goals = SavingsGoals::select('savings_goals.id', 'users.name as name', 'savings_goals.title', 'savings_goals.target_amount', 'savings_goals.current_amount', 'savings_goals.deadline')
            ->join('users', 'savings_goals.user_id', '=', 'users.id')->get();

        if ($savings_goals->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Savings Goals not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Savings Goals retrieved successfully ',
            'data' => $savings_goals
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validateData = $request->validate([
            'user_id' => 'required',
            'title' => 'required',
            'target_amount' => 'required',
            'current_amount' => 'required',
            'deadline' => 'required',
        ]);

        $savings_goals = SavingsGoals::create($validateData);
        return response()->json([
            'status' => 'success',
            'message' => 'Savings Goals created successfully ',
            'data' => $savings_goals
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(SavingsGoals $savingsGoals, $id)
    {
        $savings_goals = SavingsGoals::select('savings_goals.id', 'users.name as name', 'savings_goals.title', 'savings_goals.target_amount', 'savings_goals.current_amount', 'savings_goals.deadline')
            ->join('users', 'savings_goals.user_id', '=', 'users.id')->where('savings_goals.id', '=', $id)->first();

        if (!$savings_goals) {
            return response()->json([
                'status' => 'error',
                'message' => 'Savings Goals with id ' . $id . ' not found',
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Savings Goals retrieved successfully ',
            'data' => $savings_goals
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SavingsGoals $savingsGoals, $id)
    {
        $savings_goals = SavingsGoals::find($id);

        if (!$savings_goals) {
            return response()->json([
                'status' => 'error',
                'message' => 'Savings Goals with ' . $id . ' not found',
            ], 404);
        }

        $input = [
            'user_id' => $request->user_id ?? $savings_goals->user_id,
            'title' => $request->title ?? $savings_goals->title,
            'target_amount' => $request->target_amount ?? $savings_goals->target_amount,
            'current_amount' => $request->current_amount ?? $savings_goals->current_amount,
            'deadline' => $request->deadline ?? $savings_goals->deadline,
        ];

        $savings_goals->update($input);

        return response()->json([
            'status' => 'success',
            'message' => 'Savings Goals updated successfully ',
            'data' => $savings_goals
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SavingsGoals $savingsGoals, $id)
    {
        $savings_goals = SavingsGoals::find($id);

        if (!$savings_goals) {
            return response()->json([
                'status' => 'error',
                'message' => 'Savings Goals with ' . $id . ' not found',
            ], 404);
        }

        $savings_goals->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Savings Goals deleted successfully ',
        ]);
    }

    public function userSavingsGoals()
    {
        $userId = Auth::id();

        $savings_goals = SavingsGoals::select(
            'savings_goals.id',
            'savings_goals.title',
            'savings_goals.target_amount',
            'savings_goals.current_amount',
            'savings_goals.deadline'
        )
            ->join('users', 'savings_goals.user_id', '=', 'users.id')
            ->where('savings_goals.user_id', $userId)
            ->get();

        if ($savings_goals->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Goal not found',
            ], 404);
        }

        // Tambahkan progress per item
        $savings_goals = $savings_goals->map(function ($goal) {
            $progress = $goal->target_amount > 0
                ? min(100, round(($goal->current_amount / $goal->target_amount) * 100, 2))
                : 0;

            return [
                'id' => $goal->id,
                'title' => $goal->title,
                'target_amount' => $goal->target_amount,
                'current_amount' => $goal->current_amount,
                'deadline' => $goal->deadline,
                'progress' => $progress,
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Goals retrieved successfully',
            'data' => $savings_goals
        ]);
    }


    public function userSavingsGoalsShow($id)
    {
        $userId = Auth::id();

        $savings_goals = SavingsGoals::select('savings_goals.id', 'users.name as name', 'savings_goals.title', 'savings_goals.target_amount', 'savings_goals.current_amount', 'savings_goals.deadline')
            ->join('users', 'savings_goals.user_id', '=', 'users.id')
            ->where('savings_goals.user_id', $userId)
            ->where('savings_goals.id', $id)
            ->first();

        if (!$savings_goals) {
            return response()->json([
                'status' => 'error',
                'message' => 'Goal not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Goal retrieved successfully ',
            'data' => $savings_goals
        ]);
    }

    public function userSavingsGoalsStore(Request $request)
    {
        $userId = Auth::id();

        $validated = $request->validate([
            'title' => 'required|string',
            'target_amount' => 'required|numeric|min:1',
            'deadline' => 'required|date',
        ]);

        $goalId = DB::table('savings_goals')->insertGetId([
            'user_id' => $userId,
            'title' => $validated['title'],
            'target_amount' => $validated['target_amount'],
            'current_amount' => 0,
            'deadline' => $validated['deadline'],
        ]);

        $goal = DB::table('savings_goals')->where('id', $goalId)->where('user_id', $userId)->first();

        return response()->json([
            'status' => 'success',
            'message' => 'Goal created successfully ',
            'data' => $goal
        ]);
    }

    public function userSavingsGoalsUpdate(Request $request, $id)
    {
        $userId = Auth::id();

        $goal = DB::table('savings_goals')->where('id', $id)->where('user_id', $userId)->first();

        if (!$goal) {
            return response()->json([
                'status' => 'error',
                'message' => 'Goal not found',
            ], 404);
        }

        $input = [
            'title' => $request->title ?? $goal->title,
            'target_amount' => $request->target_amount ?? $goal->target_amount,
            'current_amount' => $request->current_amount ?? $goal->current_amount,
            'deadline' => $request->deadline ?? $goal->deadline,
        ];

        DB::table('savings_goals')->where('id', $id)->where('user_id', $userId)->update($input);

        $goalId = DB::table('savings_goals')->where('id', $id)->where('user_id', $userId)->first();

        return response()->json([
            'status' => 'success',
            'message' => 'Goal updated successfully ',
            'data' => $goalId
        ]);
    }

    public function userSavingsGoalsDestroy($id)
    {
        $userId = Auth::id();

        $goal = DB::table('savings_goals')->where('id', $id)->where('user_id', $userId)->first();

        if (!$goal) {
            return response()->json([
                'status' => 'error',
                'message' => 'Goal not found',
            ], 404);
        }

        DB::table('savings_goals')->where('id', $id)->where('user_id', $userId)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Goal deleted successfully ',
        ]);
    }
}
