# php-mqtt/laravel-client

**:warning: Work in progress - use at own risk! :warning:** 

`php-mqtt/laravel-client` was created by, and is maintained by [Namoshek](https://github.com/namoshek).
It is a Laravel wrapper for the [`php-mqtt/client`](https://github.com/php-mqtt/client) package and
allows you to connect to an MQTT broker where you can publish messages and subscribe to topics.

## Installation

```bash
composer require php-mqtt/laravel-client
```

You should then publish the configuration file using

```bash
php artisan vendor:publish --provider="PhpMqtt\Client\MqttClientServiceProvider"
```

## Usage

// TODO: usage

## Features

This library allows you to use all the features provided by [`php-mqtt/client`](https://github.com/php-mqtt/client).

## License

`php-mqtt/laravel-client` is open-sourced software licensed under the [MIT license](LICENSE.md).
