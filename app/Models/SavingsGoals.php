<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingsGoals extends Model
{
    use HasFactory;
    protected $table = 'savings_goals';
    protected $fillable = [
        'user_id',
        'title',
        'target_amount',
        'current_amount',
        'deadline'
    ];
    public $timestamps = false;
}
