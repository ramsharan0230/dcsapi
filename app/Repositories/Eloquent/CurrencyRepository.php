<?php

namespace App\Repositories\Eloquent;

use App\Models\Currency;
use App\Repositories\CurrencyInterface;
use Illuminate\Support\Collection;

class CurrencyRepository extends BaseRepository implements CurrencyInterface
{

   /**
    * CurrencyRepository constructor.
    *
    * @param Currency $model
    */
   public function __construct(Currency $model)
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

   public function getSpecificBySlug($slug){
        return $this->model->where('slug', $slug)->with(['stock', 'baseRate'])->first();
   }

   public function find($id){
    return $this->model->find($id);
}
}