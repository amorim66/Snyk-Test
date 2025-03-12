<?php
/**
 * API para gerenciamento de produtos
 * 
 * Este arquivo fornece endpoints para gerenciamento de produtos
 */

// Inicializa a sessão
session_start();

// Inclui arquivos necessários
require_once __DIR__ . '/../../src/Database.php';
require_once __DIR__ . '/../../src/Authentication.php';
require_once __DIR__ . '/../../src/models/Product.php';
require_once __DIR__ . '/../../src/controllers/ProductController.php';

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

// Verifica se o usuário é administrador para operações de escrita
$isAdmin = isset($currentUser['role']) && $currentUser['role'] === 'admin';

// Inicializa o controlador de produtos
$productController = new ProductController($db);

// Define o cabeçalho de resposta como JSON
header('Content-Type: application/json');

// Processa a requisição
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';
$productId = isset($_GET['id']) ? (int)$_GET['id'] : null;

try {
    switch ($method) {
        case 'GET':
            // Endpoints GET
            if ($productId) {
                // Obtém detalhes de um produto
                $product = $productController->getProduct($productId);
                
                if (!$product) {
                    throw new Exception('Produto não encontrado');
                }
                
                echo json_encode($product);
            } else {
                // Lista produtos
                $filters = [];
                
                if (isset($_GET['category'])) {
                    $filters['category'] = $_GET['category'];
                }
                
                if (isset($_GET['search'])) {
                    $filters['search'] = $_GET['search'];
                }
                
                $products = $productController->listProducts($filters);
                echo json_encode(['products' => $products]);
            }
            break;
            
        case 'POST':
            // Endpoints POST - Requer permissão de administrador
            if (!$isAdmin) {
                header('HTTP/1.1 403 Forbidden');
                echo json_encode(['error' => 'Permissão negada']);
                exit;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Cria um novo produto
            $result = $productController->createProduct($data);
            echo json_encode($result);
            break;
            
        case 'PUT':
            // Endpoints PUT - Requer permissão de administrador
            if (!$isAdmin) {
                header('HTTP/1.1 403 Forbidden');
                echo json_encode(['error' => 'Permissão negada']);
                exit;
            }
            
            if (!$productId) {
                throw new Exception('ID do produto não informado');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Atualiza um produto existente
            $result = $productController->updateProduct($productId, $data);
            echo json_encode($result);
            break;
            
        case 'DELETE':
            // Endpoints DELETE - Requer permissão de administrador
            if (!$isAdmin) {
                header('HTTP/1.1 403 Forbidden');
                echo json_encode(['error' => 'Permissão negada']);
                exit;
            }
            
            if (!$productId) {
                throw new Exception('ID do produto não informado');
            }
            
            // Remove um produto
            $result = $productController->deleteProduct($productId);
            echo json_encode(['success' => $result]);
            break;
            
        default:
            throw new Exception('Método não suportado');
    }
} catch (Exception $e) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => $e->getMessage()]);
} 