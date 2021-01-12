<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Formatters\Document\TransportFormatter;

class FormatterProvider extends ServiceProvider{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(){
        
        $this->app->singleton('Formatters/Document/transport', function ($app) {
            return new TransportFormatter();
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