<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budgets extends Model
{
    use HasFactory;

    protected $table = 'budgets';
    protected $fillable = [
        'user_id',
        'category_id',
        'amount',
        'month'
    ];
    public $timestamps = false;
}
