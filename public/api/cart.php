<?php
/**
 * API para gerenciamento do carrinho de compras
 * 
 * Este arquivo fornece endpoints para gerenciamento do carrinho de compras
 */

// Inicializa a sessão
session_start();

// Inclui arquivos necessários
require_once __DIR__ . '/../../src/Database.php';
require_once __DIR__ . '/../../src/Authentication.php';
require_once __DIR__ . '/../../src/models/Cart.php';
require_once __DIR__ . '/../../src/models/Product.php';
require_once __DIR__ . '/../../src/controllers/CartController.php';

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

// Inicializa o controlador de carrinho
$cartController = new CartController($db);

// Define o cabeçalho de resposta como JSON
header('Content-Type: application/json');

// Processa a requisição
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    switch ($method) {
        case 'GET':
            // Endpoints GET
            switch ($action) {
                case 'get_cart':
                    // Obtém o carrinho do usuário
                    $cart = $cartController->getCart($currentUser['id']);
                    echo json_encode($cart);
                    break;
                    
                default:
                    throw new Exception('Ação não suportada');
            }
            break;
            
        case 'POST':
            // Endpoints POST
            $data = json_decode(file_get_contents('php://input'), true);
            
            switch ($action) {
                case 'add_item':
                    // Adiciona um item ao carrinho
                    if (!isset($data['product_id'])) {
                        throw new Exception('ID do produto não informado');
                    }
                    
                    $quantity = isset($data['quantity']) ? (int)$data['quantity'] : 1;
                    
                    $result = $cartController->addToCart(
                        $currentUser['id'],
                        $data['product_id'],
                        $quantity
                    );
                    
                    echo json_encode($result);
                    break;
                    
                case 'update_item':
                    // Atualiza a quantidade de um item no carrinho
                    if (!isset($data['product_id']) || !isset($data['quantity'])) {
                        throw new Exception('ID do produto ou quantidade não informados');
                    }
                    
                    $result = $cartController->updateCartItem(
                        $currentUser['id'],
                        $data['product_id'],
                        (int)$data['quantity']
                    );
                    
                    echo json_encode($result);
                    break;
                    
                case 'remove_item':
                    // Remove um item do carrinho
                    if (!isset($data['product_id'])) {
                        throw new Exception('ID do produto não informado');
                    }
                    
                    $result = $cartController->removeFromCart(
                        $currentUser['id'],
                        $data['product_id']
                    );
                    
                    echo json_encode($result);
                    break;
                    
                case 'clear_cart':
                    // Limpa o carrinho
                    $result = $cartController->clearCart($currentUser['id']);
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