<?php

namespace LaravelDownloadUtil;

use LaravelDownloadUtil\Console\Commands\PruneOutdatedFiles;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/download-util.php' => config_path('download-util.php'),
            ], 'config');

            $this->commands([
                PruneOutdatedFiles::class,
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/download-util.php', 'download-util');
    }
}
