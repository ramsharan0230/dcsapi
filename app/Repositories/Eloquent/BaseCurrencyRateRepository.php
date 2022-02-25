<?php

namespace App\Repositories\Eloquent;

use App\Models\BaseCurrencyRate;
use App\Repositories\Eloquent\BaseRepository;
use Illuminate\Support\Collection;

class BaseCurrencyRateRepository extends BaseRepository implements BaseCurrencyRateInterface
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
}