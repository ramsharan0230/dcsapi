<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\BaseInterface; 
use App\Repositories\CurrencyInterface; 
use App\Repositories\StockInterface; 
use App\Repositories\Eloquent\CurrencyRepository; 
use App\Repositories\Eloquent\StockRepository; 
use App\Repositories\Eloquent\BaseRepository; 

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(BaseInterface::class, BaseRepository::class);
        $this->app->bind(CurrencyInterface::class, CurrencyRepository::class);
        $this->app->bind(StockInterface::class, StockRepository::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
