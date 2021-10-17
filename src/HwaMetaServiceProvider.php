<?php

namespace Hwavina\HwaMeta;

use Hwavina\HwaMeta\Console\Commands\HwaMetaCommand;
use Hwavina\HwaMeta\Console\Commands\HwaMigrateCommand;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;

class HwaMetaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/hwa_meta.php', 'hwa-meta');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/hwa_meta.php' => config_path('hwa_meta.php')
        ], 'hwa-meta');

        $this->commands([
            HwaMetaCommand::class,
            HwaMigrateCommand::class,
        ]);

        $this->app->booted(function () {
            Artisan::call('vendor:publish', [
                '--provider' => 'Hwavina\HwaMeta\HwaMetaServiceProvider',
                '--tag' => 'hwa-meta'
            ]);
        });
    }
}
