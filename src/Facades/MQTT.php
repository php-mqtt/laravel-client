<?php

declare(strict_types=1);

namespace PhpMqtt\Client\Facades;

use Illuminate\Support\Facades\Facade;
use PhpMqtt\Client\ConnectionManager;
use PhpMqtt\Client\MQTTClient;

/**
 * @method static MQTTClient connection(string $name = null)
 * @method static void close(string $connection = null)
 * @method static void publish(string $topic, string $message, string $connection = null)
 *
 * @see ConnectionManager
 * @package PhpMqtt\Client\Facades
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
