<?php
namespace Grav\Plugin\Panier;

use PDO;

class Database
{
    private string $host;
    private string $database;
    private string $user;
    private string $password;

    private PDO $connection;

    public function __construct(string $host, string $database, string $user, string $password)
    {
        $this->host = $host;
        $this->database = $database;
        $this->user = $user;
        $this->password = $password; 
    }

    public function getConnection(): PDO
    {
        if(isset($this->connection) != null)
        {
            return $this->connection;
        }

        $connection = new PDO("mysql:host=".$this->host.";dbname=".$this->database,$this->user,$this->password,[PDO::ATTR_PERSISTENT => true]);
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->connection = $connection;

        return $this->connection;
    }
}