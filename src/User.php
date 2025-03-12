<?php
namespace MinhaEmpresa;

class User {
    private $id;
    private $username;
    private $email;
    private $role;
    
    public function __construct($id, $username, $email, $role = 'user') {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->role = $role;
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function getUsername() {
        return $this->username;
    }
    
    public function getEmail() {
        return $this->email;
    }
    
    public function getRole() {
        return $this->role;
    }
    
    public function isAdmin() {
        return $this->role === 'admin';
    }
    
    public static function findByUsername(Database $db, $username) {
        // Vulnerabilidade: SQL Injection
        $sql = "SELECT * FROM users WHERE username = '$username'";
        $result = $db->query($sql);
        
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
} 