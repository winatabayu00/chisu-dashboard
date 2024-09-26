<?php

namespace Winata\PackageBased;

use Winata\PackageBased\Database\Providers\DbServiceProvider;
use Illuminate\Support\ServiceProvider;

class BaseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/winata/package-based.php', 'winata.package-based');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPublishing();
        $this->app->register(DbServiceProvider::class, true);
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     * @author Winata Bayu <winatabayu01@gmail.com>
     */
    private function registerPublishing()
    {
        $this->publishes([__DIR__ . '/../config/winata/package-based.php' => config_path('winata/package-based.php')], 'config');
    }

}
