<?php
namespace MinhaEmpresa\Security;

class InputValidator {
    /**
     * Sanitiza uma string removendo caracteres perigosos
     * 
     * @param string $input String para sanitizar
     * @return string String sanitizada
     */
    public static function sanitizeString($input) {
        if (!is_string($input)) {
            throw new \InvalidArgumentException('Input deve ser uma string');
        }
        
        // Remove caracteres não imprimíveis
        $input = preg_replace('/[\x00-\x1F\x7F]/u', '', $input);
        
        // Remove possíveis tags HTML
        $input = strip_tags($input);
        
        // Remove espaços extras
        $input = trim($input);
        
        return $input;
    }
    
    /**
     * Valida um email
     * 
     * @param string $email Email para validar
     * @return bool True se válido, false caso contrário
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Sanitiza um array recursivamente
     * 
     * @param array $array Array para sanitizar
     * @return array Array sanitizado
     */
    public static function sanitizeArray($array) {
        $result = [];
        
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result[$key] = self::sanitizeArray($value);
            } else if (is_string($value)) {
                $result[$key] = self::sanitizeString($value);
            } else {
                $result[$key] = $value;
            }
        }
        
        return $result;
    }
    
    /**
     * Valida um nome de arquivo
     * 
     * @param string $filename Nome do arquivo
     * @return bool True se válido, false caso contrário
     */
    public static function validateFilename($filename) {
        return preg_match('/^[a-zA-Z0-9_.-]+$/', $filename) === 1;
    }
    
    /**
     * Escapa HTML de forma segura
     * 
     * @param string $input String para escapar
     * @return string String escapada
     */
    public static function escapeHtml($input) {
        return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
} 