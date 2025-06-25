<?php

namespace App\Http\Controllers;

use App\Models\Transactions;
use Auth;
use DB;
use Illuminate\Http\Request;

class TransactionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $transactions = Transactions::select('transactions.id', 'users.name', 'categories.name as categories', 'transactions.amount', 'transactions.date', 'transactions.note')
            ->join('users', 'transactions.user_id', '=', 'users.id')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->get();

        if ($transactions->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transactions not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Transactions retrieved successfully ',
            'data' => $transactions
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
            'date' => 'required',
        ]);

        $transactions = Transactions::create($validateData);
        return response()->json([
            'status' => 'success',
            'message' => 'Transactions created successfully ',
            'data' => $transactions
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Transactions $transactions, $id)
    {
        $transactions = Transactions::select('users.name', 'categories.name as categories', 'transactions.amount', 'transactions.date', 'transactions.note')
            ->join('users', 'transactions.user_id', '=', 'users.id')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.id', '=', $id)
            ->first();

        if (!$transactions) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transactions with id ' . $id . ' not found',
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Transactions retrieved successfully ',
            'data' => $transactions
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transactions $transactions, $id)
    {
        $transactions = Transactions::find($id);

        if (!$transactions) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transactions with ' . $id . ' not found',
            ], 404);
        }

        $input = [
            'user_id' => $request->user_id ?? $transactions->user_id,
            'category_id' => $request->category_id ?? $transactions->category_id,
            'amount' => $request->amount ?? $transactions->amount,
            'date' => $request->date ?? $transactions->date,
            'note' => $request->note ?? $transactions->note,
        ];

        $transactions->update($input);

        return response()->json([
            'status' => 'success',
            'message' => 'Transactions updated successfully ',
            'data' => $transactions
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transactions $transactions, $id)
    {
        $transactions = Transactions::find($id);

        if (!$transactions) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transactions with ' . $id . ' not found',
            ], 404);
        }

        $transactions->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Transactions deleted successfully ',
        ]);
    }

    public function getIncomeAndExpenseSummary()
    {
        $userId = Auth::id();

        // Total pemasukan
        $totalIncome = DB::table('transactions')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.user_id', $userId)
            ->where('categories.type', 'pemasukan')
            ->sum('transactions.amount');

        // Total pengeluaran
        $totalExpense = DB::table('transactions')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.user_id', $userId)
            ->where('categories.type', 'pengeluaran')
            ->sum('transactions.amount');

        $totalBalance = $totalExpense + $totalIncome;

        return response()->json([
            'status' => 'success',
            'message' => 'Income and expense summary retrieved successfully',
            'data' => [
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'total_balance' => $totalBalance
            ]
        ]);
    }

    public function report(Request $request)
    {
        $userId = Auth::id();
        $period = $request->query('period', 'weekly'); 

        // Setup waktu awal dan akhir berdasarkan periode
        switch ($period) {
            case 'monthly':
                $startDate = now()->startOfMonth();   
                $endDate = now()->endOfMonth();     
                break;

            case 'yearly':
                $startDate = now()->startOfYear();
                $endDate = now()->endOfYear();
                break;

            default: 
                $startDate = now()->startOfWeek();
                $endDate = now()->endOfWeek();
                break;
        }

        // Total pemasukan
        $totalIncome = DB::table('transactions')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.user_id', $userId)
            ->where('categories.type', 'pemasukan')
            ->whereBetween('transactions.date', [$startDate, $endDate])
            ->sum('transactions.amount');

        // Total pengeluaran
        $totalExpense = DB::table('transactions')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.user_id', $userId)
            ->where('categories.type', 'pengeluaran')
            ->whereBetween('transactions.date', [$startDate, $endDate])
            ->sum('transactions.amount');


        return response()->json([
            'status' => 'success',
            'message' => 'Transaction report retrieved successfully',
            'data' => [
                'period' => $period,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense
            ]
        ]);
    }

    public function userTransactions()
    {
        $userId = Auth::id();

        $transactions = DB::table('transactions')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->join('users', 'transactions.user_id', '=', 'users.id')
            ->select('transactions.id','transactions.category_id', 'categories.name as categories', 'transactions.amount', 'transactions.date', 'transactions.note')
            ->where('transactions.user_id', $userId)
            ->orderBy('transactions.date', 'desc')
            ->get();

        if ($transactions->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transactions not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Transactions retrieved successfully ',
            'data' => $transactions
        ]);
    }

    public function userTransactionsShow(Request $request, $id)
    {
        $userId = Auth::id();

        $transaction = DB::table('transactions')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->select('transactions.id','transactions.category_id', 'categories.name as category_name', 'transactions.amount', 'transactions.date', 'transactions.note')
            ->where('transactions.user_id', $userId)
            ->where('transactions.id', $id)
            ->first();

        if (!$transaction) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transactions not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Transactions retrieved successfully ',
            'data' => $transaction
        ]);
    }

    public function userTransactionsStore(Request $request)
    {

        $userId = Auth::id();

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric',
            'note' => 'nullable|string',
            'date' => 'required|date',
        ]);

        $validated['user_id'] = Auth::id();

        $category = DB::table('categories')
            ->where('id', $request->category_id)
            ->where('user_id', $userId)
            ->first();

        if (!$category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid category for this user',
            ], 403);
        }

        $transactions = Transactions::create($validated);
        return response()->json([
            'status' => 'success',
            'message' => 'Transactions created successfully ',
            'data' => $transactions
        ], 200);
    }

    public function userTransactionsUpdate(Request $request, $id)
    {
        $userId = Auth::id();

        // Ambil transaksi
        $transactions = DB::table('transactions')
            ->where('id', $id)
            ->first();

        if (!$transactions || $transactions->user_id != $userId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transactions not found',
            ], 404);
        }
        $category_id = $request->category_id ?? $transactions->category_id;

        $category = DB::table('categories')
            ->where('id', $category_id)
            ->where('user_id', $userId)
            ->first();

        if (!$category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid category for this user',
            ], 403);
        }


        // Siapkan input
        $input = [
            'category_id' => $category_id,
            'amount' => $request->amount ?? $transactions->amount,
            'date' => $request->date ?? $transactions->date,
            'note' => $request->note ?? $transactions->note,
        ];

        // Update transaksi
        DB::table('transactions')
            ->where('id', $id)
            ->update($input);

        // Ambil ulang data transaksi yang sudah diupdate
        $updatedTransaction = DB::table('transactions')->where('id', $id)->first();

        return response()->json([
            'status' => 'success',
            'message' => 'Transactions updated successfully',
            'data' => $updatedTransaction
        ]);
    }

    public function userTransactionsDestroy($id)
    {
        $userId = Auth::id();

        $deleted = DB::table('transactions')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->delete();

        if ($deleted) {
            return response()->json([
                'status' => 'success',
                'message' => 'Transactions deleted successfully',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Transactions not found',
        ], 404);
    }

}