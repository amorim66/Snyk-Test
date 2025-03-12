<?php
/**
 * API para gerenciamento de pedidos
 * 
 * Este arquivo fornece endpoints para gerenciamento de pedidos
 */

// Inicializa a sessão
session_start();

// Inclui arquivos necessários
require_once __DIR__ . '/../../src/Database.php';
require_once __DIR__ . '/../../src/Authentication.php';
require_once __DIR__ . '/../../src/models/Order.php';
require_once __DIR__ . '/../../src/controllers/OrderController.php';

// Inicializa a conexão com o banco de dados
$db = new Database();

// Verifica autenticação
$auth = new Authentication($db);
if (!$auth->isLoggedIn()) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

// Obtém o usuário atual
$currentUser = $auth->getCurrentUser();

// Verifica se o usuário é administrador para operações administrativas
$isAdmin = isset($currentUser['role']) && $currentUser['role'] === 'admin';

// Inicializa o controlador de pedidos
$orderController = new OrderController($db);

// Define o cabeçalho de resposta como JSON
header('Content-Type: application/json');

// Processa a requisição
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : null;

try {
    switch ($method) {
        case 'GET':
            // Endpoints GET
            if ($orderId) {
                // Obtém detalhes de um pedido
                // Administradores podem ver qualquer pedido, usuários comuns apenas os seus
                $userId = $isAdmin ? null : $currentUser['id'];
                $order = $orderController->getOrder($orderId, $userId);
                
                if (!$order) {
                    throw new Exception('Pedido não encontrado ou acesso negado');
                }
                
                echo json_encode($order);
            } else {
                // Lista pedidos do usuário
                // Administradores podem ver todos os pedidos se o parâmetro 'all' for fornecido
                $userId = $currentUser['id'];
                
                if ($isAdmin && isset($_GET['all']) && $_GET['all'] === 'true') {
                    $userId = null;
                }
                
                $filters = [];
                
                if (isset($_GET['status'])) {
                    $filters['status'] = $_GET['status'];
                }
                
                if (isset($_GET['date_from'])) {
                    $filters['date_from'] = $_GET['date_from'];
                }
                
                if (isset($_GET['date_to'])) {
                    $filters['date_to'] = $_GET['date_to'];
                }
                
                $orders = $orderController->listOrders($userId, $filters);
                echo json_encode(['orders' => $orders]);
            }
            break;
            
        case 'POST':
            // Endpoints POST
            $data = json_decode(file_get_contents('php://input'), true);
            
            switch ($action) {
                case 'update_status':
                    // Atualiza o status de um pedido - Apenas administradores
                    if (!$isAdmin) {
                        header('HTTP/1.1 403 Forbidden');
                        echo json_encode(['error' => 'Permissão negada']);
                        exit;
                    }
                    
                    if (!$orderId || !isset($data['status'])) {
                        throw new Exception('ID do pedido ou status não informados');
                    }
                    
                    $result = $orderController->updateOrderStatus($orderId, $data['status']);
                    echo json_encode($result);
                    break;
                    
                case 'cancel':
                    // Cancela um pedido
                    if (!$orderId) {
                        throw new Exception('ID do pedido não informado');
                    }
                    
                    // Administradores podem cancelar qualquer pedido, usuários comuns apenas os seus
                    $userId = $isAdmin ? null : $currentUser['id'];
                    
                    $result = $orderController->cancelOrder($orderId, $userId);
                    echo json_encode($result);
                    break;
                    
                default:
                    throw new Exception('Ação não suportada');
            }
            break;
            
        default:
            throw new Exception('Método não suportado');
    }
} catch (Exception $e) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => $e->getMessage()]);
} 