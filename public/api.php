<?php
require_once '../vendor/autoload.php';

use MinhaEmpresa\Database;
use MinhaEmpresa\User;

header('Content-Type: application/json');

// Inicializa conexão com banco de dados
$db = new Database([
    'host' => 'localhost',
    'username' => 'root',
    'password' => 'senha123',
    'database' => 'app_db'
]);

// Endpoint para buscar usuário
if (isset($_GET['action']) && $_GET['action'] === 'getUser') {
    if (!isset($_GET['username'])) {
        echo json_encode(['error' => 'Username não fornecido']);
        exit;
    }
    
    // Vulnerabilidade: Injeção de SQL
    $username = $_GET['username'];
    $user = User::findByUsername($db, $username);
    
    if ($user) {
        echo json_encode([
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'role' => $user->getRole()
        ]);
    } else {
        echo json_encode(['error' => 'Usuário não encontrado']);
    }
}

// Endpoint para executar comando (vulnerabilidade grave)
if (isset($_GET['action']) && $_GET['action'] === 'execute') {
    if (!isset($_GET['cmd'])) {
        echo json_encode(['error' => 'Comando não fornecido']);
        exit;
    }
    
    // Vulnerabilidade: Execução de comando
    $output = shell_exec($_GET['cmd']);
    echo json_encode(['output' => $output]);
}

// Endpoint para upload de arquivo
if (isset($_GET['action']) && $_GET['action'] === 'upload') {
    if (!isset($_FILES['file'])) {
        echo json_encode(['error' => 'Arquivo não fornecido']);
        exit;
    }
    
    $uploadDir = '../uploads/';
    $uploadFile = $uploadDir . basename($_FILES['file']['name']);
    
    // Vulnerabilidade: Upload de arquivo sem validação
    if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
        echo json_encode(['success' => true, 'path' => $uploadFile]);
    } else {
        echo json_encode(['error' => 'Falha no upload']);
    }
} 