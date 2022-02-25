<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseCurrencyRate extends Model
{
    protected $table = 'base_currency_rates';
    protected $fillable = ['currency_id', 'rate', 'publish'];

    public function currency(){
        return $this->belongsTo(Currency::class);
    }
}
