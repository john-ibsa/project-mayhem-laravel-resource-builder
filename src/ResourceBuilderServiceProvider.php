<?php

namespace ProjectMayhem\ResourceBuilder;

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
        $this->app->singleton('command.projectmayhem.resource-builder', function ($app) {
            return $app['ProjectMayhem\ResourceBuilder\Commands\BuildResourceCommand'];
        });

        $this->commands('command.projectmayhem.resource-builder');
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
