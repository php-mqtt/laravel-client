# php-mqtt/laravel-client

**:warning: Work in progress - use at own risk! :warning:** 

`php-mqtt/laravel-client` was created by, and is maintained by [Namoshek](https://github.com/namoshek).
It is a Laravel wrapper for the [`php-mqtt/client`](https://github.com/php-mqtt/client) package and
allows you to connect to an MQTT broker where you can publish messages and subscribe to topics.

## Installation

```bash
composer require php-mqtt/laravel-client
```

The package will register itself through Laravel auto discovery of packages.
Registered will be the service provider as well as an `MQTT` facade.

After installing the package, you should publish the configuration file using

```bash
php artisan vendor:publish --provider="PhpMqtt\Client\MqttClientServiceProvider"
```

and change the configuration in `config/mqtt-client.php` according to your needs.

## Configuration

The package allows you to configure multiple named connections. An initial example
can be found in the published configuration file. Except for the `host` parameter,
all configuration options are entirely optional and come with the defaults provided 
to the `env()` helper in the example configuration file (no default means `null`).

An example configuration of two connections, where one is meant for sharing public
data and one for private data, could look like the following:
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

## Usage

### Publish (QoS level 0)

Publishing a message with QoS level 0 is quite easy and can be done in a single command:
```php
use PhpMqtt\Client\Facades\MQTT;

MQTT::publish('some/topic', 'Hello World!');
```

If needed, the connection name can be passed as third parameter:
```php
use PhpMqtt\Client\Facades\MQTT;

MQTT::publish('some/topic', 'Hello World!', 'public');
```

Using `MQTT::publish($topic, $message)` will implicitly call `MQTT::connection()`,
but the connection will not be closed after usage. If you want to close the connection
manually because your script does not need the connection any more, you can call
`MQTT:close()` (optionally with the connection name as a parameter).
Please also note that using `MQTT::publish($topic, $message)` will always use QoS level 0.
If you need a different QoS level, then please follow the instructions below.

### Publish (QoS level 1 & 2)

Different to QoS level 0, we need to run an event loop in order for QoS 1 and 2 to work.
This is because with a one-off command we cannot guarantee that a message reaches it's target.
The event loop will ensure a published message gets sent again if no acknowledgment is returned
by the broker within a grace period (in case of QoS level 1). Also handled by the event loop will
be the release of packages in case of QoS level 2.

```php
use PhpMqtt\Client\Facades\MQTT;

$mqtt = MQTT::connection();
$mqtt->publish('some/topic', 'foo', 1);
$mqtt->publish('some/other/topic', 'bar', 2);
$mqtt->loop(true);
```

In order to escape the loop, you can call `$mqtt->interrupt()` which will exit the loop during
the next iteration. The method can for example be called in a registered signal handler:
```php
pcntl_signal(SIGINT, function (int $signal, $info) use ($mqtt) {
    $mqtt->interrupt();
});
```

### Subscribe

Very similar to publishing with QoS level 1 and 2, subscribing requires to run an event loop.
But before running the loop, topics need to be subscribed to:

```php
use PhpMqtt\Client\Facades\MQTT;

$mqtt = MQTT::connection();
$mqtt->subscribe('some/topic', function (string $topic, string $message) {
    echo sprintf('Received QoS level 1 message on topic [%s]: %s', $topic, $message);
}, 1);
$mqtt->loop(true);
```

## Features

This library allows you to use all the features provided by [`php-mqtt/client`](https://github.com/php-mqtt/client).

## License

`php-mqtt/laravel-client` is open-sourced software licensed under the [MIT license](LICENSE.md).
