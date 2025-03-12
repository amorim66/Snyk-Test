<?php
namespace MinhaEmpresa;

class Database {
    private $connection;
    private $config;
    
    public function __construct(array $config) {
        $this->config = $config;
        $this->connect();
    }
    
    private function connect() {
        $this->connection = new \mysqli(
            $this->config['host'],
            $this->config['username'],
            $this->config['password'],
            $this->config['database']
        );
        
        if ($this->connection->connect_error) {
            die("Falha na conexão: " . $this->connection->connect_error);
        }
    }
    
    public function query($sql) {
        // Vulnerabilidade: SQL Injection
        return $this->connection->query($sql);
    }
    
    public function escape($value) {
        return $this->connection->real_escape_string($value);
    }
    
    public function close() {
        $this->connection->close();
    }
} 