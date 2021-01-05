<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Elasticsearch\Indices;
use App\Repositories\Elasticsearch\Cat;
use App\Repositories\Elasticsearch\Document;

class ElasticsearchRepositoryProvider extends ServiceProvider{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(){
        
        $this->app->singleton('Repository\Elasticsearch\Indices', function ($app) {
            return new Indices();
        });

        $this->app->singleton('Repository\Elasticsearch\Cat', function ($app) {
            return new Cat();
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
