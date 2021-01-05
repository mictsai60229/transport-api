<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Formatters\ProductFormatter;
use App\Formatters\BaseFormatter;

class FormatterProvider extends ServiceProvider{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(){
        
        $this->app->singleton('Formatter/product', function ($app) {
            return new ProductFormatter();
        });

        $this->app->singleton('Formatter/base', function ($app) {
            return new BaseFormatter();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(){
        //
    }
}