<?php
require_once '../vendor/autoload.php';

use MinhaEmpresa\Database;
use MinhaEmpresa\User;
use MinhaEmpresa\Security\InputValidator;

header('Content-Type: application/json');

// Inicializa conexão com banco de dados
$db = new Database([
    'host' => getenv('DB_HOST') ?: 'localhost',
    'username' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASS') ?: '',
    'database' => getenv('DB_NAME') ?: 'app_db'
]);

// Endpoint para buscar usuário
if (isset($_GET['action']) && $_GET['action'] === 'getUser') {
    if (!isset($_GET['username'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Username não fornecido']);
        exit;
    }
    
    // Correção: Uso de prepared statements e validação de entrada
    try {
        $username = InputValidator::sanitizeString($_GET['username']);
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Filtra dados sensíveis antes de retornar
            echo json_encode([
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role']
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Usuário não encontrado']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro interno do servidor']);
        error_log($e->getMessage());
    }
}

// Endpoint para executar comando - REMOVIDO POR SEGURANÇA
if (isset($_GET['action']) && $_GET['action'] === 'execute') {
    http_response_code(403);
    echo json_encode(['error' => 'Operação não permitida']);
    exit;
}

// Endpoint para upload de arquivo
if (isset($_GET['action']) && $_GET['action'] === 'upload') {
    if (!isset($_FILES['file'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Arquivo não fornecido']);
        exit;
    }
    
    try {
        // Validação do arquivo
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        $file = $_FILES['file'];
        
        // Validações de segurança
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Tipo de arquivo não permitido');
        }
        
        if ($file['size'] > $maxSize) {
            throw new Exception('Arquivo muito grande');
        }
        
        // Gera nome seguro para o arquivo
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFilename = bin2hex(random_bytes(16)) . '.' . $extension;
        
        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $uploadFile = $uploadDir . $newFilename;
        
        // Move o arquivo com permissões seguras
        if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
            chmod($uploadFile, 0644);
            echo json_encode([
                'success' => true,
                'filename' => $newFilename
            ]);
        } else {
            throw new Exception('Falha no upload do arquivo');
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
        error_log($e->getMessage());
    }
} 