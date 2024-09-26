<?php

namespace Winata\Core\Response;

use Illuminate\Support\ServiceProvider;
use Winata\Core\Response\Events\OnErrorEvent;
use Winata\Core\Response\Exception\ReportableException;
use Winata\Core\Response\Listeners\OnErrorEvent\StoreToDatabase;
use Winata\Core\Response\Listeners\OnErrorEvent\SendToTelegram;

class BaseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/winata/response.php', 'winata.response');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

        $this->app->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            ReportableException::class
        );

        $this->app->events->listen(
            config('winata.response.log_event_class'),
            SendToTelegram::class
        );

        $this->publishes([__DIR__ . '/../config/winata/response.php' => config_path('winata/response.php')], 'config');
    }
}
