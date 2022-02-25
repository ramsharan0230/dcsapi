<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $table = 'currencies';
    protected $fillable = ['name', 'symbol', 'publish'];

    public function baseRate(){
        return $this->hasOne(BaseCurrencyRate::class);
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    public function stock(){
        return $this->hasOne(Stock::class, 'currency_id');
    }
}
