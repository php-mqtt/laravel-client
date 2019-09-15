<?php

declare(strict_types=1);

namespace PhpMqtt\Client;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use PhpMqtt\Client\Contracts\Repository;
use PhpMqtt\Client\Exceptions\ConnectingToBrokerFailedException;
use PhpMqtt\Client\Exceptions\ConnectionNotAvailableException;
use PhpMqtt\Client\Exceptions\DataTransferException;

/**
 * Manages the MQTT connections of the application.
 *
 * @package PhpMqtt\Client
 */
class ConnectionManager
{
    /** @var Application */
    protected $application;

    /** @var array */
    protected $config;

    /** @var string */
    protected $defaultConnection;

    /** @var MQTTClient[] */
    protected $connections = [];

    /**
     * ConnectionManager constructor.
     *
     * @param Application $application
     * @param array       $config
     */
    public function __construct(Application $application, array $config)
    {
        $this->application       = $application;
        $this->config            = $config;
        $this->defaultConnection = array_get($config, 'default_connection', 'default');
    }

    /**
     * Gets the connection with the specified name.
     *
     * @param string|null $name
     * @return MQTTClient
     * @throws BindingResolutionException
     * @throws ConnectingToBrokerFailedException
     * @throws ConnectionNotAvailableException
     */
    public function connection(string $name = null): MQTTClient
    {
        if ($name === null) {
            $name = $this->defaultConnection;
        }

        if (!array_key_exists($name, $this->connections)) {
            $this->connections[$name] = $this->createConnection($name);
        }

        return $this->connections[$name];
    }

    /**
     * Closes the given connection if opened.
     *
     * @param string|null $connection
     * @throws DataTransferException
     */
    public function close(string $connection = null): void
    {
        if ($connection === null) {
            $connection = $this->defaultConnection;
        }

        if (array_key_exists($connection, $this->connections)) {
            $this->connections[$connection]->close();
            unset($this->connections[$connection]);
        }
    }

    /**
     * Publishes a message on the given connection. The QoS level will be 0
     * and the message will not be retained by the broker.
     *
     * @param string      $topic
     * @param string      $message
     * @param string|null $connection
     * @throws BindingResolutionException
     * @throws ConnectingToBrokerFailedException
     * @throws ConnectionNotAvailableException
     * @throws DataTransferException
     */
    public function publish(string $topic, string $message, string $connection = null): void
    {
        $client = $this->connection($connection);

        $client->publish($topic, $message);
    }

    /**
     * Creates a new MQTT client and connects to the specified server.
     *
     * @param string $name
     * @return MQTTClient
     * @throws BindingResolutionException
     * @throws ConnectingToBrokerFailedException
     * @throws ConnectionNotAvailableException
     */
    protected function createConnection(string $name): MQTTClient
    {
        $config = array_get($this->config, "connections.{$name}");
        if ($config === null) {
            throw new ConnectionNotAvailableException($name);
        }

        $host           = array_get($config, 'host');
        $port           = array_get($config, 'port', 1883);
        $username       = array_get($config, 'username');
        $password       = array_get($config, 'password');
        $clientId       = array_get($config, 'client_id');
        $caFile         = array_get($config, 'cafile');
        $cleanSession   = array_get($config, 'clean_session', true);
        $loggingEnabled = array_get($config, 'logging_enabled', true);
        $repository     = array_get($config, 'repository', Repository::class);

        $settings   = $this->parseConnectionSettings(array_get($config, 'settings', []));
        $repository = $this->application->make($repository);
        $logger     = $loggingEnabled ? $this->application->make('log') : null;

        $client = new MQTTClient($host, $port, $clientId, $caFile, $repository, $logger);
        $client->connect($username, $password, $settings, $cleanSession);

        return $client;
    }

    /**
     * Parses the given settings and returns a populated settings object.
     *
     * @param array $settings
     * @return ConnectionSettings
     */
    protected function parseConnectionSettings(array $settings): ConnectionSettings
    {
        $qos             = array_get($settings, 'quality_of_service', 0);
        $blockSocket     = array_get($settings, 'block_socket', false);
        $keepAlive       = array_get($settings, 'keep_alive', 10);
        $socketTimeout   = array_get($settings, 'socket_timeout', 5);
        $resendTimeout   = array_get($settings, 'resend_timeout', 10);
        $retain          = array_get($settings, 'retain', false);
        $lastWillTopic   = array_get($settings, 'last_will_topic');
        $lastWillMessage = array_get($settings, 'last_will_message');

        return new ConnectionSettings($qos, $retain, $blockSocket, $socketTimeout, $keepAlive, $resendTimeout, $lastWillTopic, $lastWillMessage);
    }
}
