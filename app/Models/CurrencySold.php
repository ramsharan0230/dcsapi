<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencySold extends Model
{
    protected $table = 'currency_solds';
    protected $fillable = ['currency_id', 'user_id', 'email', 'quantity', 'rate', 'amount'];
}
