<?php

namespace App\Repositories\Eloquent;

use App\Models\Stock;
use App\Models\Currency;
use App\Repositories\StockInterface;
use Illuminate\Support\Collection;

class StockRepository extends BaseRepository implements StockInterface
{

   /**
    * StockRepository constructor.
    *
    * @param Stock $model
    */
   public function __construct(Stock $model)
   {
       parent::__construct($model);
   }

   /**
    * @return Collection
    */
   public function all(): Collection
   {
       return $this->model->all();    
   }

   public function getSpecificById($id){
        return $this->model->find($id);
   }

   public function getStockByCurrency($currency_id){
        return Stock::where('currency_id', $currency_id)->first();
   }
}