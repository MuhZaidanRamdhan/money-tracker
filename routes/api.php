<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\BudgetsController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\SavingsGoalsController;
use App\Http\Controllers\SavingsTransactionsController;
use App\Http\Controllers\TransactionsController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/password/otp', [AuthController::class, 'sendOtp']);
Route::post('/password/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/password/reset', [AuthController::class, 'resetPassword']);


Route::middleware(['auth:sanctum'])->group(function () {
  
  Route::post('/logout', [AuthController::class, 'logout']);
  Route::post('/password/change-password', [AuthController::class, 'changePassword']);
});


Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {

  Route::get('/users', [UserController::class, 'index']);
  Route::get('/users/{id}', [UserController::class, 'show']);
  Route::post('/users', [UserController::class, 'store']);
  Route::put('/users/{id}', [UserController::class, 'update']);
  Route::delete('/users/{id}', [UserController::class, 'destroy']);

  Route::get('/categories', [CategoriesController::class, 'index']);
  Route::get('/categories/{id}', [CategoriesController::class, 'show']);
  Route::post('/categories', [CategoriesController::class, 'store']);
  Route::put('/categories/{id}', [CategoriesController::class, 'update']);
  Route::delete('/categories/{id}', [CategoriesController::class, 'destroy']);

  Route::get('/transactions', [TransactionsController::class, 'index']);
  Route::get('/transactions/{id}', [TransactionsController::class, 'show']);
  Route::post('/transactions', [TransactionsController::class, 'store']);
  Route::put('/transactions/{id}', [TransactionsController::class, 'update']);
  Route::delete('/transactions/{id}', [TransactionsController::class, 'destroy']);

  Route::get('/budgets', [BudgetsController::class, 'index']);
  Route::get('/budgets/{id}', [BudgetsController::class, 'show']);
  Route::post('/budgets', [BudgetsController::class, 'store']);
  Route::put('/budgets/{id}', [BudgetsController::class, 'update']);
  Route::delete('/budgets/{id}', [BudgetsController::class, 'destroy']);

  Route::get('/savings_goals', [SavingsGoalsController::class, 'index']);
  Route::get('/savings_goals/{id}', [SavingsGoalsController::class, 'show']);
  Route::post('/savings_goals', [SavingsGoalsController::class, 'store']);
  Route::put('/savings_goals/{id}', [SavingsGoalsController::class, 'update']);
  Route::delete('/savings_goals/{id}', [SavingsGoalsController::class, 'destroy']);

  Route::get('/savings_transactions', [SavingsTransactionsController::class, 'index']);
  Route::get('/savings_transactions/{id}', [SavingsTransactionsController::class, 'show']);
  Route::post('/savings_transactions', [SavingsTransactionsController::class, 'store']);
  Route::put('/savings_transactions/{id}', [SavingsTransactionsController::class, 'update']);
  Route::delete('/savings_transactions/{id}', [SavingsTransactionsController::class, 'destroy']);

});

Route::middleware(['auth:sanctum', 'role:user,admin'])->group(function () {

  Route::get('/user/user_profile', [UserController::class, 'getUser']);
  Route::put('/user/user_profile', [UserController::class, 'updateUser']);

  Route::get('/user/transactions', [TransactionsController::class, 'userTransactions']);
  Route::get('/user/{id}/transactions', [TransactionsController::class, 'userTransactionsShow']);
  Route::post('/user/transactions', [TransactionsController::class, 'userTransactionsStore']);
  Route::put('/user/{id}/transactions', [TransactionsController::class, 'userTransactionsUpdate']);
  Route::delete('/user/{id}/transactions', [TransactionsController::class, 'userTransactionsDestroy']);
  Route::get('user/transactions/summary', [TransactionsController::class, 'getIncomeAndExpenseSummary']);
  Route::get('user/transactions/report', [TransactionsController::class, 'report']);

  Route::get('/user/categories', [CategoriesController::class, 'userCategories']);
  Route::get('/user/categories/type', [CategoriesController::class, 'userCategoriesByType']);
  Route::get('/user/categories/{categoryId}/transactions', [CategoriesController::class, 'userCategoryTransactions']);
  Route::get('/user/{id}/categories', [CategoriesController::class, 'userCategoriesShow']);
  Route::post('/user/categories', [CategoriesController::class, 'userCategoriesStore']);
  Route::put('/user/{id}/categories', [CategoriesController::class, 'userCategoriesUpdate']);
  Route::delete('/user/{id}/categories', [CategoriesController::class, 'userCategoriesDestroy']);


  Route::get('/user/budgets', [BudgetsController::class, 'userBudgets']);
  Route::post('/user/budgets', [BudgetsController::class, 'userBudgetsStore']);
  // Route::get('/user/budgets/{id}', [BudgetsController::class, 'userBudgetsShow']);
  Route::get('/user/{id}/budgets', [BudgetsController::class, 'userBudgetsShow']);
  Route::get('/user/budgets/progress', [BudgetsController::class, 'userBudgetsProgress']);
  Route::put('/user/{id}/budgets', [BudgetsController::class, 'userBudgetsUpdate']);
  Route::delete('/user/{id}/budgets', [BudgetsController::class, 'userBudgetsDestroy']);


  Route::get('/user/savings_goals', [SavingsGoalsController::class, 'userSavingsGoals']);
  Route::get('/user/{id}/savings_goals', [SavingsGoalsController::class, 'userSavingsGoalsShow']);
  Route::post('/user/savings_goals', [SavingsGoalsController::class, 'userSavingsGoalsStore']);
  Route::put('/user/{id}/savings_goals', [SavingsGoalsController::class, 'userSavingsGoalsUpdate']);
  Route::delete('/user/{id}/savings_goals', [SavingsGoalsController::class, 'userSavingsGoalsDestroy']);

  Route::get('/user/savings_transactions', [SavingsTransactionsController::class, 'userSavingsTransactions']);
  Route::get('/user/{id}/savings_transactions', [SavingsTransactionsController::class, 'userSavingsTransactionsShow']);
  Route::post('/user/savings_transactions', [SavingsTransactionsController::class, 'userSavingsTransactionsStore']);
  Route::put('/user/{id}/savings_transactions', [SavingsTransactionsController::class, 'userSavingsTransactionsUpdate']);
  Route::delete('/user/{id}/savings_transactions', [SavingsTransactionsController::class, 'userSavingsTransactionsDestroy']);

});