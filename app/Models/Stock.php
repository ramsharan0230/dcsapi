<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{

    protected $table = 'stocks';
    protected $fillable = ['currency_id', 'quantity', 'publish'];

    public function currency(){
        return $this->belongsTo(Currency::class);
    }
}
