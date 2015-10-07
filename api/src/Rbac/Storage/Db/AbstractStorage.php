<?php


namespace Spira\Rbac\Storage\Db;


use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\ConnectionResolverInterface;

abstract class AbstractStorage
{
    /**
     * @var ConnectionResolverInterface
     */
    private $connection;

    public function __construct(ConnectionResolverInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return ConnectionInterface
     */
    public function getConnection()
    {
        return $this->connection->connection();
    }

}