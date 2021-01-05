# php-mqtt/laravel-client

[![Latest Stable Version](https://poser.pugx.org/php-mqtt/laravel-client/v)](https://packagist.org/packages/php-mqtt/laravel-client)
[![Total Downloads](https://poser.pugx.org/php-mqtt/laravel-client/downloads)](https://packagist.org/packages/php-mqtt/laravel-client)
[![Tests](https://github.com/php-mqtt/laravel-client/workflows/Tests/badge.svg)](https://github.com/php-mqtt/laravel-client/actions?query=workflow%3ATests)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=php-mqtt_laravel-client&metric=alert_status)](https://sonarcloud.io/dashboard?id=php-mqtt_laravel-client)
[![Maintainability Rating](https://sonarcloud.io/api/project_badges/measure?project=php-mqtt_laravel-client&metric=sqale_rating)](https://sonarcloud.io/dashboard?id=php-mqtt_laravel-client)
[![Reliability Rating](https://sonarcloud.io/api/project_badges/measure?project=php-mqtt_laravel-client&metric=reliability_rating)](https://sonarcloud.io/dashboard?id=php-mqtt_laravel-client)
[![Security Rating](https://sonarcloud.io/api/project_badges/measure?project=php-mqtt_laravel-client&metric=security_rating)](https://sonarcloud.io/dashboard?id=php-mqtt_laravel-client)
[![Vulnerabilities](https://sonarcloud.io/api/project_badges/measure?project=php-mqtt_laravel-client&metric=vulnerabilities)](https://sonarcloud.io/dashboard?id=php-mqtt_laravel-client)
[![License](https://poser.pugx.org/php-mqtt/laravel-client/license)](https://packagist.org/packages/php-mqtt/laravel-client)

`php-mqtt/laravel-client` was created by, and is maintained by [Marvin Mall](https://github.com/namoshek).
It is a Laravel wrapper for the [`php-mqtt/client`](https://github.com/php-mqtt/client) package and
allows you to connect to an MQTT broker where you can publish messages and subscribe to topics.

## Installation

The package is available on [packagist.org](https://packagist.org/packages/php-mqtt/laravel-client) and can be installed using composer:

```bash
composer require php-mqtt/laravel-client
```

The package will register itself through Laravel auto discovery of packages.
Registered will be the service provider as well as an `MQTT` facade.

After installing the package, you should publish the configuration file using

```bash
php artisan vendor:publish --provider="PhpMqtt\Client\MqttClientServiceProvider" --tag="config"
```

and change the configuration in `config/mqtt-client.php` according to your needs.

## Configuration

The package allows you to configure multiple named connections. An initial example with inline documentation can be found in the published configuration file.
Most of the configuration options are optional and come with sane defaults (especially all of the `connection_settings`).

An example configuration of two connections, where one is meant for sharing public data and one for private data, could look like the following:
```php
'default_connection' => 'private',

'connections' => [
    'private' => [
        'host' => 'mqtt.example.com',
        'port' => 1883,
    ],
    'public' => [
        'host' => 'test.mosquitto.org',
        'port' => 1883,
    ],
],
```
In this example, the private connection is the default one.

_Note: it is recommended to use environment variables to configure the MQTT client. Available environment variables can be found in the configuration file._

## Usage

### Publish (QoS level 0)

Publishing a message with QoS level 0 is quite easy and can be done in a single command:
```php
use PhpMqtt\Client\Facades\MQTT;

MQTT::publish('some/topic', 'Hello World!');
```

If needed, the _retain_ flag (default: `false`) can be passed as third and the connection name as fourth parameter:
```php
use PhpMqtt\Client\Facades\MQTT;

MQTT::publish('some/topic', 'Hello World!', true, 'public');
```

Using `MQTT::publish($topic, $message)` will implicitly call `MQTT::connection()`, but the connection will not be closed after usage.
If you want to close the connection manually because your script does not need the connection anymore,
you can call `MQTT:disconnect()` (optionally with the connection name as a parameter).
Please also note that using `MQTT::publish($topic, $message)` will always use QoS level 0.
If you need a different QoS level, you will need to use the `MqttClient` directly which is explained below.

### Publish (QoS level 1 & 2)

Different to QoS level 0, we need to run an event loop in order for QoS 1 and 2 to work.
This is because with a one-off command we cannot guarantee that a message reaches its target.
The event loop will ensure a published message gets sent again if no acknowledgment is returned by the broker within a grace period.
Only when the broker returns an acknowledgement (or all of the acknowledgements in case of QoS 2),
the client will stop resending the message.

```php
use PhpMqtt\Client\Facades\MQTT;

/** @var \PhpMqtt\Client\Contracts\MqttClient $mqtt */
$mqtt = MQTT::connection();
$mqtt->publish('some/topic', 'foo', 1);
$mqtt->publish('some/other/topic', 'bar', 2, true); // Retain the message
$mqtt->loop(true);
```

`$mqtt->loop()` actually starts an infinite loop. To escape it, there are multiple options.
In case of simply publishing a message, all we want is to receive an acknowledgement.
Therefore, we can simply pass `true` as second parameter to exit the loop as soon as all resend queues are cleared:

```php
/** @var \PhpMqtt\Client\Contracts\MqttClient $mqtt */
$mqtt->loop(true, true);
```

In order to escape the loop, you can also call `$mqtt->interrupt()` which will exit the loop during
the next iteration. The method can, for example, be called in a registered signal handler:
```php
/** @var \PhpMqtt\Client\Contracts\MqttClient $mqtt */
pcntl_signal(SIGINT, function () use ($mqtt) {
    $mqtt->interrupt();
});
```

### Subscribe

Very similar to publishing with QoS level 1 and 2, subscribing requires to run an event loop.
Although before running the loop, topics need to be subscribed to:

```php
use PhpMqtt\Client\Facades\MQTT;

/** @var \PhpMqtt\Client\Contracts\MqttClient $mqtt */
$mqtt = MQTT::connection();
$mqtt->subscribe('some/topic', function (string $topic, string $message) {
    echo sprintf('Received QoS level 1 message on topic [%s]: %s', $topic, $message);
}, 1);
$mqtt->loop(true);
```

## Features

This library allows you to use all the features provided by [`php-mqtt/client`](https://github.com/php-mqtt/client).
Simply retrieve an instance of `\PhpMqtt\Client\Contracts\MqttClient` with `MQTT::connection(string $name = null)` and use it directly.

For an extensive collection of examples which explain how to use the MQTT client (directly),
you can visit the [`php-mqtt/client-examples` repository](https://github.com/php-mqtt/client-examples).

## License

`php-mqtt/laravel-client` is open-source software licensed under the [MIT license](LICENSE.md).
