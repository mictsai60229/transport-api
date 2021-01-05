<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Elasticsearch\Indices;
use App\Services\Elasticsearch\Bulk;

class ElasticsearchServiceProvider extends ServiceProvider{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(){
        
        $this->app->singleton('Service\Elasticsearch\Indices', function ($app) {
            return new Indices();
        });
        $this->app->singleton('Service\Elasticsearch\Bulk', function ($app) {
            return new Bulk();
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