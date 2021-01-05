<?php

namespace PhpMqtt\Client;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use PhpMqtt\Client\Contracts\Repository;
use PhpMqtt\Client\Repositories\MemoryRepository;

/**
 * Registers the php-mqtt/laravel-client within the application.
 *
 * @package PhpMqtt\Client
 */
class MqttClientServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/mqtt-client.php', 'mqtt-client');

        $this->registerServices();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     * @throws \BadFunctionCallException
     */
    public function boot(): void
    {
        if (!function_exists('config_path')) {
            throw new \BadFunctionCallException('config_path() function not found. Is the Laravel framework installed?');
        }

        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/../config/mqtt-client.php' => config_path('mqtt-client.php')], 'config');
        }
    }

    /**
     * Registers the services offered by this package.
     *
     * @return void
     */
    protected function registerServices(): void
    {
        $this->app->bind(ConnectionManager::class, function (Application $app) {
            $config = $app->make('config')->get('mqtt-client', []);

            return new ConnectionManager($app, $config);
        });

        $this->app->bind(Repository::class, MemoryRepository::class);
    }
}
