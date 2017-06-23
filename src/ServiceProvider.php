<?php

namespace OneMustCode\LaravelDDD;

use DIJ\DDD\Commands\CreateCommand;
use DIJ\DDD\Commands\ProjectCommand;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerConsoleCommands();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Register the doctrine service provider
        $this->app->register(\LaravelDoctrine\ORM\DoctrineServiceProvider::class);

        // Register the twigbridge service provider
        $this->app->register(\TwigBridge\ServiceProvider::class);
    }

    /**
     * Registers all defined console commands
     */
    private function registerConsoleCommands()
    {
        $this->commands([
            ProjectCommand::class,
            CreateCommand::class,
        ]);
    }
}