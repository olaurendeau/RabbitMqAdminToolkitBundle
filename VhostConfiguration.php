<?php

namespace Ola\RabbitMqAdminToolkitBundle;

use RabbitMq\ManagementApi\Client;

class VhostConfiguration
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $configuration;

    /**
     * @var boolean
     */
    private $deleteAllowed;

    /**
     * @param Client $client
     * @param string $name
     * @param array $configuration
     * @param boolean $deleteAllowed
     */
    public function __construct(Client $client, $name, array $configuration, $deleteAllowed)
    {
        $this->client = $client;
        $this->name = $name;
        $this->configuration = $configuration;
        $this->deleteAllowed = $deleteAllowed;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getConfiguration($key = null)
    {
        if (null !== $key && isset($this->configuration[$key])) {
            return $this->configuration[$key];
        }

        return $this->configuration;
    }

    /**
     * @return boolean
     */
    public function isDeleteAllowed()
    {
        if (isset($this->configuration['delete_allowed'])) {
            return $this->configuration['delete_allowed'];
        }

        return $this->deleteAllowed;
    }
}
