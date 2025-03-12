<?php
/**
 * API para integração com marketplaces
 * 
 * Este arquivo fornece endpoints para integração com diferentes marketplaces
 */

// Inicializa a sessão
session_start();

// Inclui arquivos necessários
require_once __DIR__ . '/../../src/Database.php';
require_once __DIR__ . '/../../src/Authentication.php';
require_once __DIR__ . '/../../src/models/Product.php';
require_once __DIR__ . '/../../src/controllers/ProductController.php';
require_once __DIR__ . '/../../src/integrations/mercado_livre/MercadoLivreAPI.php';
require_once __DIR__ . '/../../src/integrations/amazon/AmazonAPI.php';
require_once __DIR__ . '/../../src/integrations/shopee/ShopeeAPI.php';
require_once __DIR__ . '/../../src/integrations/magalu/MagaluAPI.php';

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

// Inicializa o controlador de produtos
$productController = new ProductController($db);

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
                case 'list_marketplaces':
                    // Lista os marketplaces disponíveis
                    echo json_encode([
                        'marketplaces' => [
                            'mercado_livre' => 'Mercado Livre',
                            'amazon' => 'Amazon',
                            'shopee' => 'Shopee',
                            'magalu' => 'Magazine Luiza'
                        ]
                    ]);
                    break;
                    
                case 'get_orders':
                    // Obtém pedidos de um marketplace
                    $marketplace = isset($_GET['marketplace']) ? $_GET['marketplace'] : '';
                    $dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : null;
                    
                    if (empty($marketplace)) {
                        throw new Exception('Marketplace não informado');
                    }
                    
                    switch ($marketplace) {
                        case 'mercado_livre':
                            $api = new MercadoLivreAPI('client_id', 'client_secret');
                            $orders = $api->getOrders($dateFrom);
                            break;
                        case 'amazon':
                            $api = new AmazonAPI('seller_id', 'access_key', 'secret_key');
                            $orders = $api->getOrders($dateFrom);
                            break;
                        case 'shopee':
                            $api = new ShopeeAPI('partner_id', 'partner_key', 'shop_id');
                            $orders = $api->getOrders($dateFrom);
                            break;
                        case 'magalu':
                            $api = new MagaluAPI('api_key', 'seller_id');
                            $orders = $api->getOrders($dateFrom);
                            break;
                        default:
                            throw new Exception('Marketplace não suportado');
                    }
                    
                    echo json_encode($orders);
                    break;
                    
                default:
                    throw new Exception('Ação não suportada');
            }
            break;
            
        case 'POST':
            // Endpoints POST
            $data = json_decode(file_get_contents('php://input'), true);
            
            switch ($action) {
                case 'sync_products':
                    // Sincroniza produtos com marketplaces
                    if (!isset($data['product_ids']) || !is_array($data['product_ids'])) {
                        throw new Exception('IDs de produtos não informados');
                    }
                    
                    if (!isset($data['marketplaces']) || !is_array($data['marketplaces'])) {
                        throw new Exception('Marketplaces não informados');
                    }
                    
                    $result = $productController->syncWithMarketplaces(
                        $data['product_ids'],
                        $data['marketplaces']
                    );
                    
                    echo json_encode(['result' => $result]);
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