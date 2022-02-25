<?php

namespace App\Repositories;

use App\Models\Currency;
use Illuminate\Support\Collection;

interface CurrencyInterface
{
   public function all(): Collection;

   public function getSpecificBySlug($slug);

   public function find($id);

}