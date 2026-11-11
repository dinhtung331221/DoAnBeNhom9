<?php

/**
 * Lop tuong thich cho ma cu. Code moi su dung App\Core\Database truc tiep.
 */
require_once __DIR__ . '/app/bootstrap.php';

class Database
{
    private $connection;

    public function __construct()
    {
        $this->connection = App\Core\Database::connection();
    }

    public function select($sql, $parameters = array())
    {
        $statement = $this->connection->prepare($sql);
        $statement->execute($parameters);
        return $statement->fetchAll();
    }

    public function execute($sql, $parameters = array())
    {
        $statement = $this->connection->prepare($sql);
        return $statement->execute($parameters);
    }

    public function connection()
    {
        return $this->connection;
    }

    public function close()
    {
        $this->connection = null;
    }
}
