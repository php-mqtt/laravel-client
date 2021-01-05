<?php

declare(strict_types=1);

namespace PhpMqtt\Client;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use PhpMqtt\Client\Contracts\Repository;
use PhpMqtt\Client\Exceptions\ConfigurationInvalidException;
use PhpMqtt\Client\Exceptions\ConnectingToBrokerFailedException;
use PhpMqtt\Client\Exceptions\ConnectionNotAvailableException;
use PhpMqtt\Client\Exceptions\DataTransferException;
use PhpMqtt\Client\Exceptions\ProtocolNotSupportedException;
use PhpMqtt\Client\Exceptions\RepositoryException;

/**
 * Manages the MQTT connections of the application.
 *
 * @package PhpMqtt\Client
 */
class ConnectionManager
{
    private Application $application;
    private array $config;
    private string $defaultConnection;

    /** @var MqttClient[] */
    private array $connections = [];

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
        $this->defaultConnection = Arr::get($config, 'default_connection', 'default');
    }

    /**
     * Gets the connection with the specified name.
     *
     * @param string|null $name
     * @return MqttClient
     * @throws BindingResolutionException
     * @throws ConfigurationInvalidException
     * @throws ConnectingToBrokerFailedException
     * @throws ConnectionNotAvailableException
     * @throws ProtocolNotSupportedException
     */
    public function connection(string $name = null): MqttClient
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
    public function disconnect(string $connection = null): void
    {
        if ($connection === null) {
            $connection = $this->defaultConnection;
        }

        if (array_key_exists($connection, $this->connections)) {
            $this->connections[$connection]->disconnect();
            unset($this->connections[$connection]);
        }
    }

    /**
     * Publishes a message on the given connection. The QoS level will be 0.
     *
     * @param string      $topic
     * @param string      $message
     * @param bool        $retain
     * @param string|null $connection
     * @throws BindingResolutionException
     * @throws ConfigurationInvalidException
     * @throws ConnectingToBrokerFailedException
     * @throws ConnectionNotAvailableException
     * @throws DataTransferException
     * @throws ProtocolNotSupportedException
     * @throws RepositoryException
     */
    public function publish(string $topic, string $message, bool $retain = false, string $connection = null): void
    {
        $client = $this->connection($connection);

        $client->publish($topic, $message, MqttClient::QOS_AT_MOST_ONCE, $retain);
    }

    /**
     * Creates a new MQTT client and connects to the specified server.
     *
     * @param string $name
     * @return MqttClient
     * @throws BindingResolutionException
     * @throws ConfigurationInvalidException
     * @throws ConnectingToBrokerFailedException
     * @throws ConnectionNotAvailableException
     * @throws ProtocolNotSupportedException
     */
    protected function createConnection(string $name): MqttClient
    {
        $config = Arr::get($this->config, "connections.{$name}");

        if ($config === null) {
            throw new ConnectionNotAvailableException($name);
        }

        $host           = Arr::get($config, 'host');
        $port           = Arr::get($config, 'port', 1883);
        $clientId       = Arr::get($config, 'client_id');
        $protocol       = Arr::get($config, 'protocol', MqttClient::MQTT_3_1);
        $cleanSession   = Arr::get($config, 'use_clean_session', true);
        $repository     = Arr::get($config, 'repository', Repository::class);
        $loggingEnabled = Arr::get($config, 'enable_logging', true);

        $settings   = $this->buildConnectionSettings(Arr::get($config, 'connection_settings', []));
        $repository = $this->application->make($repository);
        $logger     = $loggingEnabled ? $this->application->make('log') : null;

        $client = new MqttClient($host, $port, $clientId, $protocol, $repository, $logger);
        $client->connect($settings, $cleanSession);

        return $client;
    }

    /**
     * Builds the {@see ConnectionSettings} for the connection specified by the given config.
     *
     * @param array $config
     * @return ConnectionSettings
     */
    protected function buildConnectionSettings(array $config): ConnectionSettings
    {
        return (new ConnectionSettings)
            ->setConnectTimeout(Arr::get($config, 'connect_timeout', 60))
            ->setSocketTimeout(Arr::get($config, 'socket_timeout', 5))
            ->setResendTimeout(Arr::get($config, 'resend_timeout', 10))
            ->setKeepAliveInterval(Arr::get($config, 'keep_alive_interval', 10))
            ->setUsername(Arr::get($config, 'auth.username'))
            ->setPassword(Arr::get($config, 'auth.password'))
            ->setUseTls(Arr::get($config, 'tls.enabled', false))
            ->setTlsSelfSignedAllowed(Arr::get($config, 'tls.allow_self_signed_certificate', false))
            ->setTlsVerifyPeer(Arr::get($config, 'tls.verify_peer', true))
            ->setTlsVerifyPeerName(Arr::get($config, 'tls.verify_peer_name', true))
            ->setTlsCertificateAuthorityFile(Arr::get($config, 'tls.ca_file'))
            ->setTlsCertificateAuthorityPath(Arr::get($config, 'tls.ca_path'))
            ->setTlsClientCertificateFile(Arr::get($config, 'tls.client_certificate_file'))
            ->setTlsClientCertificateKeyFile(Arr::get($config, 'tls.client_certificate_key_file'))
            ->setTlsClientCertificateKeyPassphrase(Arr::get($config, 'tls.client_certificate_key_passphrase'))
            ->setLastWillTopic(Arr::get($config, 'last_will.topic'))
            ->setLastWillMessage(Arr::get($config, 'last_will.message'))
            ->setLastWillQualityOfService(Arr::get($config, 'last_will.quality_of_service', MqttClient::QOS_AT_MOST_ONCE))
            ->setRetainLastWill(Arr::get($config, 'last_will.retain', false));
    }
}
