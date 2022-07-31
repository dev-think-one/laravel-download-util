<?php

namespace LaravelDownloadUtil\Tests;

use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            \LaravelDownloadUtil\ServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set(
            'filesystems.disks',
            array_merge($app['config']->get('filesystems.disks'), [
                'prunable_storage' => [
                    'driver'     => 'local',
                    'root'       => storage_path('app/public/files-download'),
                    'url'        => env('APP_URL').'/storage/files-download',
                    'visibility' => 'public',
                ],
            ])
        );
    }
}
