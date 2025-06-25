<?php

namespace App\Http\Controllers;

use App\Models\SavingsTransactions;
use Auth;
use DB;
use Illuminate\Http\Request;

class SavingsTransactionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $savings_transactions = SavingsTransactions::select('savings_transactions.id', 'users.name as name', 'savings_goals.title as title', 'savings_transactions.user_id', 'savings_transactions.amount', 'savings_transactions.date', 'savings_transactions.type', 'savings_transactions.note')
            ->join('savings_goals', 'savings_transactions.savings_goals_id', '=', 'savings_goals.id')
            ->join('users', 'savings_transactions.user_id', '=', 'users.id')
            ->get();

        if ($savings_transactions->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Savings Transactions not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Savings Transactions retrieved successfully ',
            'data' => $savings_transactions
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validateData = $request->validate([
            'savings_goals_id' => 'required',
            'user_id' => 'required',
            'amount' => 'required',
            'type' => 'required',
            'date' => 'required'
        ]);

        $savings_transactions = SavingsTransactions::create($validateData);
        return response()->json([
            'status' => 'success',
            'message' => 'Savings Transactions created successfully ',
            'data' => $savings_transactions
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(SavingsTransactions $savingsTransactions, $id)
    {
        $savings_transactions = SavingsTransactions::select('savings_transactions.id', 'users.name as name', 'savings_goals.title as title', 'savings_transactions.user_id', 'savings_transactions.amount', 'savings_transactions.date', 'savings_transactions.type', 'savings_transactions.note')
            ->join('savings_goals', 'savings_transactions.savings_goals_id', '=', 'savings_goals.id')
            ->join('users', 'savings_transactions.user_id', '=', 'users.id')
            ->where('savings_transactions.id', '=', $id)->first();

        if (!$savings_transactions) {
            return response()->json([
                'status' => 'error',
                'message' => 'Savings Transactions with id ' . $id . ' not found',
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Savings Transactions retrieved successfully ',
            'data' => $savings_transactions
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SavingsTransactions $savingsTransactions, $id)
    {
        $savings_transactions = SavingsTransactions::find($id);

        if (!$savings_transactions) {
            return response()->json([
                'status' => 'error',
                'message' => 'Savings Transactions with ' . $id . ' not found',
            ], 404);
        }

        $input = [
            'savings_goals_id' => $request->savings_goals_id ?? $savings_transactions->savings_goals_id,
            'user_id' => $request->user_id ?? $savings_transactions->user_id,
            'amount' => $request->amount ?? $savings_transactions->amount,
            'type' => $request->type ?? $savings_transactions->type,
            'date' => $request->date ?? $savings_transactions->date,
            'note' => $request->note ?? $savings_transactions->note,
        ];

        $savings_transactions->update($input);

        return response()->json([
            'status' => 'success',
            'message' => 'Savings Transactions updated successfully ',
            'data' => $savings_transactions
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SavingsTransactions $savingsTransactions, $id)
    {
        $savings_transactions = SavingsTransactions::find($id);

        if (!$savings_transactions) {
            return response()->json([
                'status' => 'error',
                'message' => 'Savings Transactions with ' . $id . ' not found',
            ], 404);
        }

        $savings_transactions->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Savings Transactions deleted successfully ',
            'data' => $savings_transactions
        ]);
    }

    public function userSavingsTransactions()
    {

        $userId = Auth::id();

        $transactions = DB::table('savings_transactions')
            ->join('savings_goals', 'savings_transactions.savings_goals_id', '=', 'savings_goals.id')
            ->join('users', 'savings_transactions.user_id', '=', 'users.id')
            ->select('savings_transactions.id', 'savings_transactions.savings_goals_id', 'savings_goals.title as title', 'savings_transactions.amount', 'savings_transactions.date', 'savings_transactions.type', 'savings_transactions.note')
            ->where('savings_goals.user_id', $userId)

            ->get();

        if ($transactions->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Savings Transactions not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Savings Transactions retrieved successfully ',
            'data' => $transactions
        ]);
    }

    public function userSavingsTransactionsShow($id)
    {

        $userId = Auth::id();

        $transaction = DB::table('savings_transactions')
            ->join('savings_goals', 'savings_transactions.savings_goals_id', '=', 'savings_goals.id')
            ->where('savings_goals.user_id', $userId)
            ->where('savings_transactions.id', $id)
            ->select('savings_transactions.id', 'savings_transactions.savings_goals_id', 'savings_goals.title as title', 'savings_transactions.amount', 'savings_transactions.date', 'savings_transactions.type', 'savings_transactions.note')
            ->first();

        if (!$transaction) {
            return response()->json([
                'status' => 'error',
                'message' => 'Savings Transactions not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Savings Transactions retrieved successfully ',
            'data' => $transaction
        ]);
    }

    public function userSavingsTransactionsStore(Request $request)
    {
        $userId = Auth::id();

        $validateData = $request->validate([
            'savings_goals_id' => 'required|integer',
            'amount' => 'required|numeric|min:1',
            'type' => 'required|in:deposit,withdraw',
            'date' => 'required|date'
        ]);

        // Cek apakah savings goal milik user
        $goal = DB::table('savings_goals')
            ->where('id', $validateData['savings_goals_id'])
            ->where('user_id', $userId)
            ->first();

        if (!$goal) {
            return response()->json([
                'status' => 'error',
                'message' => 'Savings goal not found or not authorized',
            ], 403);
        }

        // Cek jika withdraw dan dana tidak cukup
        if ($validateData['type'] === 'withdraw' && $validateData['amount'] > $goal->current_amount) {
            return response()->json([
                'status' => 'error',
                'message' => 'Saldo tidak cukup',
            ], 400);
        }

        // Simpan transaksi
        $transactionId = DB::table('savings_transactions')->insertGetId([
            'user_id' => $userId,
            'savings_goals_id' => $validateData['savings_goals_id'],
            'amount' => $validateData['amount'],
            'type' => $validateData['type'],
            'date' => $validateData['date'],
            'note' => $request->note,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Update saldo goals
        if ($validateData['type'] === 'deposit') {
            DB::table('savings_goals')
                ->where('id', $validateData['savings_goals_id'])
                ->increment('current_amount', $validateData['amount']);
        } elseif ($validateData['type'] === 'withdraw') {
            DB::table('savings_goals')
                ->where('id', $validateData['savings_goals_id'])
                ->decrement('current_amount', $validateData['amount']);
        }

        $transactions = DB::table('savings_transactions')->where('id', $transactionId)->where('user_id', $userId)->first();

        return response()->json([
            'status' => 'success',
            'message' => 'Savings transaction created successfully',
            'data' => $transactions
        ], 200);

    }

    public function userSavingsTransactionsUpdate(Request $request, $id)
    {
        $userId = Auth::id();

        $transaction = DB::table('savings_transactions')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$transaction) {
            return response()->json([
                'status' => 'error',
                'message' => 'Savings Transaction not found',
            ], 404);
        }

        // Data input yang akan diupdate
        $input = [
            'savings_goals_id' => $request->savings_goals_id ?? $transaction->savings_goals_id,
            'amount' => $request->amount ?? $transaction->amount,
            'type' => $request->type ?? $transaction->type,
            'date' => $request->date ?? $transaction->date,
            'note' => $request->note ?? $transaction->note,
        ];

        // Ambil goals terkait dan pastikan milik user
        $goal = DB::table('savings_goals')
            ->where('id', $input['savings_goals_id'])
            ->where('user_id', $userId)
            ->first();

        if (!$goal) {
            return response()->json([
                'status' => 'error',
                'message' => 'Savings goal not found or not authorized',
            ], 403);
        }

        // Step 1: Rollback efek dari transaksi lama
        if ($transaction->type === 'deposit') {
            DB::table('savings_goals')->where('id', $transaction->savings_goals_id)
                ->decrement('current_amount', $transaction->amount);
        } elseif ($transaction->type === 'withdraw') {
            DB::table('savings_goals')->where('id', $transaction->savings_goals_id)
                ->increment('current_amount', $transaction->amount);
        }

        // Step 2: Cek jika withdraw baru melebihi saldo sekarang
        $updatedGoal = DB::table('savings_goals')->where('id', $input['savings_goals_id'])->first();
        if ($input['type'] === 'withdraw' && $input['amount'] > $updatedGoal->current_amount) {
            // Kembalikan saldo lama karena update gagal
            if ($transaction->type === 'deposit') {
                DB::table('savings_goals')->where('id', $transaction->savings_goals_id)
                    ->increment('current_amount', $transaction->amount);
            } elseif ($transaction->type === 'withdraw') {
                DB::table('savings_goals')->where('id', $transaction->savings_goals_id)
                    ->decrement('current_amount', $transaction->amount);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Saldo tidak cukup untuk withdraw baru',
            ], 400);
        }

        // Step 3: Terapkan efek transaksi baru
        if ($input['type'] === 'deposit') {
            DB::table('savings_goals')->where('id', $input['savings_goals_id'])
                ->increment('current_amount', $input['amount']);
        } elseif ($input['type'] === 'withdraw') {
            DB::table('savings_goals')->where('id', $input['savings_goals_id'])
                ->decrement('current_amount', $input['amount']);
        }

        // Step 4: Update transaksi
        DB::table('savings_transactions')
            ->where('id', $id)
            ->update(array_merge($input, [
                'updated_at' => now()
            ]));

        $transactions = DB::table('savings_transactions')->where('id', $id)->where('user_id', $userId)->first();

        return response()->json([
            'status' => 'success',
            'message' => 'Savings Transaction updated successfully',
            'data' => $transactions
        ]);

    }

    public function userSavingsTransactionsDestroy($id)
    {
        $userId = Auth::id();

        $savings_transactions = DB::table('savings_transactions')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$savings_transactions) {
            return response()->json([
                'status' => 'error',
                'message' => 'Savings Transactions not found',
            ], 404);
        }

        DB::table('savings_transactions')
            ->where('id', $id)
            ->delete();

        DB::table('savings_goals')
            ->where('id', $savings_transactions->savings_goals_id)
            ->decrement('current_amount', $savings_transactions->amount);

        return response()->json([
            'status' => 'success',
            'message' => 'Savings Transactions deleted successfully ',
        ], 200);
    }
}