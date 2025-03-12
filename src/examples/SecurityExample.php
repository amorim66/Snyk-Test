<?php

require_once __DIR__ . '/../utils/SecurityUtils.php';

// Exemplo de uso das funções com vulnerabilidades leves
class SecurityExample {
    public function registerUser($username, $password) {
        // Vulnerabilidade leve: Armazenamento de senha com hash fraco
        $hashedPassword = SecurityUtils::hashPassword($password);
        
        // Vulnerabilidade leve: Cookie inseguro
        SecurityUtils::setUserCookie($username);
        
        // Vulnerabilidade leve: Token fraco
        $token = SecurityUtils::generateToken();
        
        // Vulnerabilidade leve: Sanitização inadequada
        $cleanUsername = SecurityUtils::sanitizeInput($username);
        
        // Vulnerabilidade leve: Criptografia fraca
        $encryptedData = SecurityUtils::encryptData($username, 'chave_secreta');
        
        // Vulnerabilidade leve: Arquivo com permissões muito abertas
        SecurityUtils::saveTemporaryFile($username, 'user_data.txt');
        
        return [
            'username' => $cleanUsername,
            'token' => $token,
            'encrypted' => $encryptedData
        ];
    }
    
    public function validateLogin($storedHash, $inputHash) {
        // Vulnerabilidade leve: Comparação insegura de hashes
        if (SecurityUtils::compareHashes($storedHash, $inputHash)) {
            // Vulnerabilidade leve: Sessão com timeout muito longo
            SecurityUtils::initSession();
            return true;
        }
        return false;
    }
} 