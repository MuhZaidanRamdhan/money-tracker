<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingsTransactions extends Model
{
    use HasFactory;
    protected $table = 'savings_transactions';
    protected $fillable = [
        'savings_goals_id',
        'user_id',
        'amount',
        'type',
        'date',
        'note'
    ];
    public $timestamps = false;
}
