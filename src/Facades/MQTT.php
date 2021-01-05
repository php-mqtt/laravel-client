<?php

declare(strict_types=1);

namespace PhpMqtt\Client\Facades;

use Illuminate\Support\Facades\Facade;
use PhpMqtt\Client\ConnectionManager;
use PhpMqtt\Client\Contracts\MqttClient;

/**
 * @method static MqttClient connection(string $name = null)
 * @method static void disconnect(string $connection = null)
 * @method static void publish(string $topic, string $message, bool $retain = false, string $connection = null)
 *
 * @package PhpMqtt\Client\Facades
 * @see ConnectionManager
 */
class MQTT extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return ConnectionManager::class;
    }
}
