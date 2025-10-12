<?php

namespace App\Services;

use LdapRecord\Connection;
use LdapRecord\Container;

/**
 * Active Directory service
 */
class ActiveDirectory
{
    /** @var \LdapRecord\Connection $connection */
    protected Connection $connection;

    /**
     * Construct the service and add it to the container
     *
     * @param array $ldapConfig
     */
    public function __construct(array $ldapConfig)
    {
        $this->connection = new Connection($ldapConfig);
        Container::addConnection($this->connection);
    }

    /**
     * Get the underlying connection
     *
     * @return \LdapRecord\Connection
     */
    public function connection(): Connection
    {
        return $this->connection;
    }
}
