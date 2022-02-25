<?php

namespace App\Repositories;

use App\Models\BaseCurrencyRate;
use Illuminate\Support\Collection;

interface BaseCurrencyRateInterface
{
   public function all(): Collection;
}