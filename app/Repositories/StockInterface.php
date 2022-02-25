<?php

namespace App\Repositories;

use App\Models\Stock;
use Illuminate\Support\Collection;

interface StockInterface
{
   public function all(): Collection;

   public function getSpecificById($id);

   public function getStockByCurrency($id);
}