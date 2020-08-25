<?php

namespace IncendiaryBlue\ResourceBuilder;

use Illuminate\Support\ServiceProvider;

class ResourceBuilderServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('command.incendiaryblue.resource-builder', function ($app) {
            return $app['IncendiaryBlue\ResourceBuilder\Commands\BuildResourceCommand'];
        });

        $this->commands('command.incendiaryblue.resource-builder');
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
