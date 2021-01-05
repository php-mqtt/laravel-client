<?php

declare(strict_types=1);

namespace PhpMqtt\Client\Exceptions;

/**
 * Class ConnectionNotAvailableException
 *
 * @package PhpMqtt\Client\Exceptions
 */
class ConnectionNotAvailableException extends MqttClientException
{
    public function __construct(string $name)
    {
        parent::__construct(sprintf('An MQTT connection with the name [%s] could not be found.', $name));
    }
}
