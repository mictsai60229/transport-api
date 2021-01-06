<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Formatters\TransportFormatter;
use App\Formatters\BaseFormatter;

class FormatterProvider extends ServiceProvider{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(){
        
        $this->app->singleton('Formatter/transport', function ($app) {
            return new TransportFormatter();
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