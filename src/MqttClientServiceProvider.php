<?php

namespace PhpMqtt\Client;

use Illuminate\Support\ServiceProvider;

/**
 * Registers the php-mqtt/laravel-client within the application.
 *
 * @package PhpMqtt\Client
 */
class MqttClientServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->handleConfigs();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind any implementations.
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return [];
    }

    /**
     * Publishes and merges the configuration of the package.
     *
     * @return void
     */
    protected function handleConfigs(): void
    {
        $configPath = __DIR__ . '/../config/mqtt-client.php';

        $this->publishes([$configPath => config_path('mqtt-client.php')], 'config');

        $this->mergeConfigFrom($configPath, 'mqtt-client');
    }
}
