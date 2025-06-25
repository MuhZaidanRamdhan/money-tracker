<?php

namespace App\Http\Controllers;

use App\Models\User;
use Auth;
use DB;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = DB::table('users')->get();

        if ($users->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Users not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Users retrieved successfully ',
            'data' => $users
        ]);
    }

    public function store(Request $request)
    {
        $validateData = $request->validate([
            'name' => 'required',
            'email' => 'required',
            'password' => 'required',
        ]);

        $user = User::create($validateData);
        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully ',
            'data' => $user
        ], 200);
    }

    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'User retrieved successfully ',
            'data' => $user
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }

        $data = [
            'name' => $request->name ?? $user->name,
            'email' => $request->email ?? $user->email,
            'password' => $request->password ?? $user->password,
            'alamat' => $request->alamat ?? $user->alamat,
            'jenis_kelamin' => $request->jenis_kelamin ?? $user->jenis_kelamin,
            'nomor_hp' => $request->nomor_hp ?? $user->nomor_hp,
        ];

        $user->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully ',
            'data' => $user
        ]);
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }

        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully ',
        ]);
    }

    public function getUser()
    {
        $userId = Auth::id();

        // Ambil data user
        $user = DB::table('users')
            ->select('id', 'name', 'email', 'alamat','jenis_kelamin','nomor_hp')
            ->where('id', $userId)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }

        // Hitung total dari masing-masing relasi
        $totalTransactions = DB::table('transactions')->where('user_id', $userId)->count();
        $totalCategories = DB::table('categories')->where('user_id', $userId)->count();
        $totalSavingsGoals = DB::table('savings_goals')->where('user_id', $userId)->count();
        $totalBudgets = DB::table('budgets')->where('user_id', $userId)->count();
        $totalSavingsTransactions = DB::table('savings_transactions')->where('user_id', $userId)->count();

        // Gabungkan data user dan statistik ke response
        return response()->json([
            'status' => 'success',
            'message' => 'User profile info retrieved successfully',
            'data' => [
                'user' => $user,
                'stats' => [
                    'total_transactions' => $totalTransactions,
                    'total_categories' => $totalCategories,
                    'total_savings_goals' => $totalSavingsGoals,
                    'total_budgets' => $totalBudgets,
                    'total_savings_transactions' => $totalSavingsTransactions
                ]
            ]
        ], 200);
    }

    public function updateUser(Request $request)
    {

        $userId = Auth::id();

        $userDetail = DB::table('users')->where('id', $userId)->first();

        if (!$userDetail) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }

        $input = [
            'name' => $request->name ?? $userDetail->name,
            'alamat' => $request->alamat ?? $userDetail->alamat,
            'nomor_hp' => $request->nomor_hp ?? $userDetail->nomor_hp,
            'jenis_kelamin' => $request->jenis_kelamin ?? $userDetail->jenis_kelamin,
            'tanggal_lahir' => $request->tanggal_lahir ?? $userDetail->tanggal_lahir
        ];

        DB::table('users')->where('id', $userId)->update($input);

        $user = DB::table('users')->select('id', 'name', 'email', 'alamat','jenis_kelamin','nomor_hp')->where('id', $userId)->first();

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully ',
            'data' => $user
        ]);
    }

}
