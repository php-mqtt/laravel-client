<?php

declare(strict_types=1);

use PhpMqtt\Client\Repositories\MemoryRepository;

return [

    /*
    |--------------------------------------------------------------------------
    | Default MQTT Connection
    |--------------------------------------------------------------------------
    |
    | This setting defines the default MQTT connection returned when requesting
    | a connection without name from the facade.
    |
    */

    'default_connection' => 'default',

    /*
    |--------------------------------------------------------------------------
    | MQTT Connections
    |--------------------------------------------------------------------------
    |
    | These are the MQTT connections used by the application. You can also open
    | an individual connection from the application itself, but all connections
    | defined here can be accessed via name conveniently.
    |
    */

    'connections' => [
        'default' => [
            'host' => env('MQTT_HOST'),
            'port' => env('MQTT_PORT', 1883),
            'username' => env('MQTT_USERNAME'),
            'password' => env('MQTT_PASSWORD'),
            'client_id' => env('MQTT_CLIENT_ID'),
            'cafile' => env('MQTT_CAFILE'),
            'clean_session' => env('MQTT_CLEAN_SESSION', true),
            'logging_enabled' => env('MQTT_LOGGING', true),
            'repository' => MemoryRepository::class,
            'settings' => [
                'quality_of_service' => env('MQTT_QUALITY_OF_SERVICE', 0),
                'block_socket' => env('MQTT_BLOCK_SOCKET', false),
                'keep_alive' => env('MQTT_KEEP_ALIVE', 10),
                'socket_timeout' => env('MQTT_TIMEOUT', 5),
                'resend_timeout' => env('MQTT_RESEND_TIMEOUT', 10),
                'retain' => env('MQTT_RETAIN', false),
                'last_will_topic' => env('MQTT_WILL_TOPIC'),
                'last_will_message' => env('MQTT_WILL_MESSAGE'),
            ],
        ],
    ],

];
