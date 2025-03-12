<?php
namespace MinhaEmpresa;

class Authentication {
    private $db;
    
    public function __construct(Database $db) {
        $this->db = $db;
    }
    
    public function login($username, $password) {
        // Vulnerabilidade: SQL Injection
        $sql = "SELECT * FROM users WHERE username = '$username' AND password = '" . md5($password) . "'";
        $result = $this->db->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $userData = $result->fetch_assoc();
            return new User(
                $userData['id'],
                $userData['username'],
                $userData['email'],
                $userData['role']
            );
        }
        
        return null;
    }
    
    public function register($username, $email, $password) {
        // Vulnerabilidade: Senha armazenada com hash fraco (MD5)
        $hashedPassword = md5($password);
        
        // Vulnerabilidade: SQL Injection
        $sql = "INSERT INTO users (username, email, password, role) 
                VALUES ('$username', '$email', '$hashedPassword', 'user')";
        
        return $this->db->query($sql);
    }
    
    public function logout() {
        session_start();
        session_destroy();
    }
} 