<?php

namespace App\Services;

use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\MqttClient;

/**
 * MQTT stateless service
 */
class Mqtt
{
    /** @var \PhpMqtt\Client\MqttClient */
    protected MqttClient $client;

    /** @var \PhpMqtt\Client\ConnectionSettings */
    protected ConnectionSettings $settings;

    /**
     * Construct MQTT service
     *
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $clientId Null will set it randomly
     * @param int    $port     Defaults to 1883
     * @param string $version  Defaults to MqttClient::MQTT_3_1_1
     *
     * @throws \PhpMqtt\Client\Exceptions\ConfigurationInvalidException
     * @throws \PhpMqtt\Client\Exceptions\ConnectingToBrokerFailedException
     * @throws \PhpMqtt\Client\Exceptions\ProtocolNotSupportedException
     */
    public function __construct(
        string $host,
        string $username,
        string $password,
        string $clientId = null,
        int $port = 1883,
        string $version = MqttClient::MQTT_3_1_1
    ) {
        $this->client = new MqttClient($host, $port, $clientId, $version);
        $this->settings = (new ConnectionSettings)
                            ->setUsername($username)
                            ->setPassword($password);
    }

    /**
     * Statelessly publish a message to a topic, then disconnects
     *
     * @param  string $topic
     * @param  string $message
     * @param  int    $qos Defaults to 0.
     * @param  bool   $retain Defaults to false.
     * @return void
     *
     * @throws \PhpMqtt\Client\Exceptions\ConfigurationInvalidException
     * @throws \PhpMqtt\Client\Exceptions\ConnectingToBrokerFailedException
     * @throws \PhpMqtt\Client\Exceptions\DataTransferException
     * @throws \PhpMqtt\Client\Exceptions\RepositoryException
     */
    public function publish(string $topic, string $message, int $qos = 0, bool $retain = false)
    {
        $this->client->connect($this->settings, true);
        $this->client->publish($topic, $message, $qos, $retain);
        $this->client->disconnect();
    }

    /**
     * Statelessly publish a message to a topic, then disconnects
     *
     * @param  string      $topic
     * @return string|null
     *
     * @throws \PhpMqtt\Client\Exceptions\ConfigurationInvalidException
     * @throws \PhpMqtt\Client\Exceptions\ConnectingToBrokerFailedException
     * @throws \PhpMqtt\Client\Exceptions\DataTransferException
     * @throws \PhpMqtt\Client\Exceptions\RepositoryException
     */
    public function readLast(string $topic): ?string
    {
        $lastMessage = null;

        $this->client->connect($this->settings, true);
        $this->client->subscribe($topic, function ($topic, $message, $wasRetained) use (&$lastMessage) {
            $lastMessage = $message;

            $this->client->interrupt();
        });
        $this->client->loop(exitWhenQueuesEmpty: true, queueWaitLimit: 1);
        $this->client->disconnect();

        return $lastMessage;
    }
}
