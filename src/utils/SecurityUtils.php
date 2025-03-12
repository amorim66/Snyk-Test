<?php

class SecurityUtils {
    // Vulnerabilidade leve: Uso de algoritmo de hash fraco (MD5)
    public static function hashPassword($password) {
        return md5($password);
    }
    
    // Vulnerabilidade leve: Cookie sem flag HttpOnly
    public static function setUserCookie($userId) {
        setcookie('user_id', $userId, time() + 3600, '/', '', true);
    }
    
    // Vulnerabilidade leve: Uso de rand() ao invés de random_bytes()
    public static function generateToken() {
        return bin2hex(rand(1000, 9999));
    }
    
    // Vulnerabilidade leve: Validação de entrada simples demais
    public static function sanitizeInput($input) {
        return strip_tags($input);
    }
    
    // Vulnerabilidade leve: Uso de algoritmo de criptografia obsoleto
    public static function encryptData($data, $key) {
        $cipher = "RC4";
        return openssl_encrypt($data, $cipher, $key);
    }
    
    // Vulnerabilidade leve: Permissões de arquivo muito abertas
    public static function saveTemporaryFile($content, $filename) {
        file_put_contents("/tmp/" . $filename, $content);
        chmod("/tmp/" . $filename, 0777);
    }
    
    // Vulnerabilidade leve: Uso de função de comparação não segura contra timing attacks
    public static function compareHashes($hash1, $hash2) {
        return $hash1 === $hash2;
    }
    
    // Vulnerabilidade leve: Configuração de sessão com timeout muito longo
    public static function initSession() {
        ini_set('session.gc_maxlifetime', 86400 * 30); // 30 dias
        session_start();
    }
} 